import os
import sqlite3
import requests
import zipfile
import tempfile
import logging
from datetime import datetime, timedelta
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse
from telegram import (
    Update,
    InlineKeyboardButton,
    InlineKeyboardMarkup,
    BotCommand
)
from telegram.ext import (
    ApplicationBuilder,
    CommandHandler,
    MessageHandler,
    ContextTypes,
    filters,
    CallbackQueryHandler,
    JobQueue
)

# ------------------- إعدادات البوت -------------------
BOT_TOKEN = "7865309137:AAHsUzdVldTzAQinr1AUrhxNotm5O1QJ7xg"
ADMIN_ID = 7627857345
DB_NAME = "bot_database.db"
REQUEST_TIMEOUT = 30
MAX_FILE_SIZE = 50 * 1024 * 1024  # 50MB
DAILY_LIMIT = 5  # الحد اليومي للاستخدام
REFERRAL_REWARD = 10  # نقاط المكافأة لكل إحالة

# إعدادات Callback
CALLBACK_POINTS = "user_points"
CALLBACK_INVITE = "user_invite"
CALLBACK_STATS = "user_stats"
CALLBACK_ADMIN_STATS = "admin_stats"
CALLBACK_ADMIN_BROADCAST = "admin_broadcast"
CALLBACK_ADMIN_CHANNELS = "admin_channels"
CALLBACK_ADMIN_USERS = "admin_users"
CALLBACK_BAN_USER = "ban_user"
CALLBACK_UNBAN_USER = "unban_user"
CALLBACK_ADD_CHANNEL = "add_channel"
CALLBACK_REMOVE_CHANNEL = "remove_channel"
CALLBACK_CONFIRM_BROADCAST = "confirm_broadcast"
CALLBACK_CANCEL_BROADCAST = "cancel_broadcast"

# إعداد التسجيل
logging.basicConfig(
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    level=logging.INFO
)
logger = logging.getLogger(__name__)

# ------------------- قاعدة البيانات -------------------
def init_db():
    conn = sqlite3.connect(DB_NAME)
    cursor = conn.cursor()
    
    # جدول المستخدمين
    cursor.execute('''CREATE TABLE IF NOT EXISTS users (
        user_id INTEGER PRIMARY KEY,
        username TEXT,
        first_name TEXT,
        last_name TEXT,
        join_date TEXT,
        points INTEGER DEFAULT 0,
        is_banned INTEGER DEFAULT 0,
        last_used TEXT,
        usage_count INTEGER DEFAULT 0,
        referral_code TEXT UNIQUE,
        referrals_count INTEGER DEFAULT 0
    )''')
    
    # جدول الإحالات
    cursor.execute('''CREATE TABLE IF NOT EXISTS referrals (
        referrer_id INTEGER,
        referred_id INTEGER,
        date TEXT,
        PRIMARY KEY (referrer_id, referred_id)
    )''')
    
    # جدول القنوات
    cursor.execute('''CREATE TABLE IF NOT EXISTS channels (
        channel_id INTEGER PRIMARY KEY,
        username TEXT,
        title TEXT,
        added_by INTEGER,
        add_date TEXT
    )''')
    
    # جدول البث
    cursor.execute('''CREATE TABLE IF NOT EXISTS broadcasts (
        broadcast_id INTEGER PRIMARY KEY AUTOINCREMENT,
        admin_id INTEGER,
        message TEXT,
        sent_date TEXT,
        users_count INTEGER
    )''')
    
    conn.commit()
    conn.close()

init_db()

# ------------------- الوظائف المساعدة -------------------
def get_db_connection():
    return sqlite3.connect(DB_NAME)

def get_user(user_id):
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM users WHERE user_id = ?", (user_id,))
    columns = [column[0] for column in cursor.description]
    user = cursor.fetchone()
    conn.close()
    return dict(zip(columns, user)) if user else None

def add_user(user):
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        referral_code = f"REF-{user.id}"
        cursor.execute('''
            INSERT OR IGNORE INTO users 
            (user_id, username, first_name, last_name, join_date, referral_code) 
            VALUES (?, ?, ?, ?, ?, ?)
        ''', (user.id, user.username, user.first_name, user.last_name, datetime.now().isoformat(), referral_code))
        conn.commit()
    except Exception as e:
        logger.error(f"Error adding user: {e}")
    finally:
        conn.close()

def update_user(user_id, **kwargs):
    conn = get_db_connection()
    cursor = conn.cursor()
    set_clause = ", ".join([f"{key} = ?" for key in kwargs])
    values = list(kwargs.values()) + [user_id]
    try:
        cursor.execute(f"UPDATE users SET {set_clause} WHERE user_id = ?", values)
        conn.commit()
    except Exception as e:
        logger.error(f"Error updating user: {e}")
    finally:
        conn.close()

def can_use_bot(user_id):
    user = get_user(user_id)
    if not user:
        return False
    
    if user['is_banned']:
        return False
    
    if user['last_used']:
        last_used = datetime.fromisoformat(user['last_used'])
        if (datetime.now() - last_used) < timedelta(days=1):
            return user['usage_count'] < DAILY_LIMIT
    return True

def record_usage(user_id):
    user = get_user(user_id)
    if not user:
        return
    
    if user['last_used']:
        last_used = datetime.fromisoformat(user['last_used'])
        if (datetime.now() - last_used) >= timedelta(days=1):
            update_user(user_id, usage_count=1, last_used=datetime.now().isoformat())
        else:
            update_user(user_id, usage_count=user['usage_count']+1, last_used=datetime.now().isoformat())
    else:
        update_user(user_id, usage_count=1, last_used=datetime.now().isoformat())

# ------------------- نظام الإحالات والنقاط -------------------
async def handle_referral(user, referrer_id, context):
    if referrer_id == user.id:
        return False

    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("SELECT 1 FROM referrals WHERE referred_id = ?", (user.id,))
        if cursor.fetchone():
            return False
        
        cursor.execute('''
            INSERT INTO referrals (referrer_id, referred_id, date)
            VALUES (?, ?, ?)
        ''', (referrer_id, user.id, datetime.now().isoformat()))
        
        cursor.execute('''
            UPDATE users 
            SET points = points + ?,
                referrals_count = referrals_count + 1
            WHERE user_id = ?
        ''', (REFERRAL_REWARD, referrer_id))
        
        conn.commit()
        
        try:
            await context.bot.send_message(
                chat_id=referrer_id,
                text=f"🎉 أحالك {user.first_name} وحصلت على {REFERRAL_REWARD} نقاط!"
            )
        except Exception as e:
            logger.error(f"Error sending referral notification: {e}")
        
        return True
    except Exception as e:
        logger.error(f"Error handling referral: {e}")
        return False
    finally:
        conn.close()

# ------------------- نظام الحظر/فك الحظر -------------------
async def ban_user_cmd(update: Update, context: ContextTypes.DEFAULT_TYPE):
    if update.effective_user.id != ADMIN_ID:
        await update.message.reply_text("⚠️ هذا الأمر للمشرف فقط!")
        return
    
    if not context.args:
        await update.message.reply_text("⚠️ يرجى تحديد ID المستخدم\nمثال: /ban 123456789")
        return
    
    try:
        user_id = int(context.args[0])
        update_user(user_id, is_banned=1)
        await update.message.reply_text(f"✅ تم حظر المستخدم {user_id} بنجاح")
    except Exception as e:
        await update.message.reply_text(f"❌ خطأ: {str(e)}")

async def unban_user_cmd(update: Update, context: ContextTypes.DEFAULT_TYPE):
    if update.effective_user.id != ADMIN_ID:
        await update.message.reply_text("⚠️ هذا الأمر للمشرف فقط!")
        return
    
    if not context.args:
        await update.message.reply_text("⚠️ يرجى تحديد ID المستخدم\nمثال: /unban 123456789")
        return
    
    try:
        user_id = int(context.args[0])
        update_user(user_id, is_banned=0)
        await update.message.reply_text(f"✅ تم فك حظر المستخدم {user_id} بنجاح")
    except Exception as e:
        await update.message.reply_text(f"❌ خطأ: {str(e)}")

# ------------------- نظام استخراج المواقع -------------------
async def scrape_website(url, user_id):
    try:
        # إنشاء مجلد مؤقت
        temp_dir = tempfile.mkdtemp(prefix=f"bot_{user_id}_")
        logger.info(f"Created temp dir: {temp_dir}")
        
        # تحميل الصفحة
        response = requests.get(url, timeout=REQUEST_TIMEOUT)
        response.raise_for_status()
        
        # حفظ الملف الرئيسي
        index_path = os.path.join(temp_dir, "index.html")
        with open(index_path, 'w', encoding='utf-8') as f:
            f.write(response.text)
        
        # إنشاء ملف ZIP
        zip_path = os.path.join(temp_dir, "website.zip")
        with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
            zipf.write(index_path, "index.html")
        
        return zip_path
    except Exception as e:
        logger.error(f"Error scraping website: {e}")
        raise Exception(f"❌ فشل في استخراج الموقع: {str(e)}")

# ------------------- معالجة الأزرار -------------------
async def button_handler(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    
    try:
        if query.data == CALLBACK_POINTS:
            await show_points(update, context)
        elif query.data == CALLBACK_INVITE:
            await show_invite(update, context)
        elif query.data == CALLBACK_STATS:
            await show_stats(update, context)
        elif query.data == CALLBACK_ADMIN_STATS:
            await admin_stats(update, context)
        elif query.data == CALLBACK_ADMIN_BROADCAST:
            await admin_broadcast_menu(update, context)
        elif query.data == CALLBACK_ADMIN_CHANNELS:
            await admin_channels_menu(update, context)
        elif query.data == CALLBACK_ADMIN_USERS:
            await admin_users_menu(update, context)
        elif query.data.startswith(CALLBACK_BAN_USER):
            user_id = int(query.data.split('_')[-1])
            update_user(user_id, is_banned=1)
            await query.edit_message_text(f"✅ تم حظر المستخدم {user_id}")
        elif query.data.startswith(CALLBACK_UNBAN_USER):
            user_id = int(query.data.split('_')[-1])
            update_user(user_id, is_banned=0)
            await query.edit_message_text(f"✅ تم فك حظر المستخدم {user_id}")
        elif query.data == CALLBACK_ADD_CHANNEL:
            await add_channel_prompt(update, context)
        elif query.data == CALLBACK_REMOVE_CHANNEL:
            await remove_channel_menu(update, context)
        elif query.data.startswith("remove_channel_"):
            channel_id = int(query.data.split('_')[-1])
            await remove_channel(update, context, channel_id)
        elif query.data == CALLBACK_CONFIRM_BROADCAST:
            await send_broadcast(update, context)
        elif query.data == CALLBACK_CANCEL_BROADCAST:
            await cancel_broadcast(update, context)
    except Exception as e:
        logger.error(f"Error in button handler: {e}")
        await query.edit_message_text("⚠️ حدث خطأ أثناء معالجة الطلب")

# ------------------- أوامر المستخدمين -------------------
async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user = update.effective_user
    add_user(user)
    
    # معالجة الإحالة
    if context.args and context.args[0].isdigit():
        referrer_id = int(context.args[0])
        await handle_referral(user, referrer_id, context)
    
    # عرض القائمة الرئيسية
    user_data = get_user(user.id)
    remaining = DAILY_LIMIT - (user_data['usage_count'] if user_data and user_data['last_used'] and 
                              (datetime.now() - datetime.fromisoformat(user_data['last_used'])) < timedelta(days=1) else 0)
    
    keyboard = [
        [InlineKeyboardButton("⚡ استخراج موقع", switch_inline_query_current_chat="")],
        [InlineKeyboardButton("📊 إحصائياتي", callback_data=CALLBACK_STATS),
         InlineKeyboardButton("🎁 نقاطي", callback_data=CALLBACK_POINTS)],
        [InlineKeyboardButton("👥 دعوة أصدقاء", callback_data=CALLBACK_INVITE)]
    ]
    
    if user.id == ADMIN_ID:
        keyboard.append([InlineKeyboardButton("👨‍💻 لوحة الأدمن", callback_data="admin_panel")])
    
    text = f"""
✨ مرحباً {user.first_name}!

• النقاط: {user_data['points'] if user_data else 0}
• المحاولات المتبقية: {remaining}/{DAILY_LIMIT}
• عدد الأحالات: {user_data['referrals_count'] if user_data else 0}

اختر أحد الخيارات:
"""
    await update.message.reply_text(text, reply_markup=InlineKeyboardMarkup(keyboard))

async def show_points(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    
    user = get_user(query.from_user.id)
    points = user['points'] if user else 0
    
    keyboard = [
        [InlineKeyboardButton("🔙 رجوع", callback_data="back_to_main")]
    ]
    
    await query.edit_message_text(
        f"🎁 نقاطك الحالية: {points}",
        reply_markup=InlineKeyboardMarkup(keyboard)
    )

async def show_invite(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    
    user = get_user(query.from_user.id)
    referral_code = user['referral_code'] if user else f"REF-{query.from_user.id}"
    
    keyboard = [
        [InlineKeyboardButton("🔙 رجوع", callback_data="back_to_main")]
    ]
    
    invite_link = f"https://t.me/your_bot_username?start={query.from_user.id}"
    text = f"""
👥 دعوة الأصدقاء

رابط الدعوة الخاص بك:
{invite_link}

كود الإحالة: {referral_code}

لكل صديق تدعوه وتحصل على {REFERRAL_REWARD} نقاط!
"""
    await query.edit_message_text(
        text,
        reply_markup=InlineKeyboardMarkup(keyboard)
    )

async def show_stats(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    
    user = get_user(query.from_user.id)
    if not user:
        await query.edit_message_text("⚠️ لم يتم العثور على بيانات المستخدم")
        return
    
    remaining = DAILY_LIMIT - (user['usage_count'] if user['last_used'] and 
                              (datetime.now() - datetime.fromisoformat(user['last_used'])) < timedelta(days=1) else 0)
    
    keyboard = [
        [InlineKeyboardButton("🔙 رجوع", callback_data="back_to_main")]
    ]
    
    text = f"""
📊 إحصائياتك:

• النقاط: {user['points']}
• المحاولات المتبقية اليوم: {remaining}/{DAILY_LIMIT}
• عدد الأحالات: {user['referrals_count']}
• تاريخ الانضمام: {user['join_date']}
"""
    await query.edit_message_text(
        text,
        reply_markup=InlineKeyboardMarkup(keyboard)
    )

async def handle_scrape(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user = update.effective_user
    user_data = get_user(user.id)
    
    if not user_data or user_data['is_banned']:
        await update.message.reply_text("⚠️ حسابك غير نشط أو محظور!")
        return
    
    if not can_use_bot(user.id):
        await update.message.reply_text(f"⚠️ لقد استخدمت جميع محاولاتك اليومية ({DAILY_LIMIT})!")
        return
    
    url = update.message.text.strip()
    if not url.startswith(('http://', 'https://')):
        await update.message.reply_text("⚠️ يرجى إرسال رابط يبدأ بـ http:// أو https://")
        return
    
    msg = await update.message.reply_text("🔄 جاري معالجة الموقع...")
    
    try:
        zip_path = await scrape_website(url, user.id)
        record_usage(user.id)
        
        with open(zip_path, 'rb') as f:
            await context.bot.send_document(
                chat_id=update.effective_chat.id,
                document=f,
                filename="website.zip",
                caption=f"✅ تم الاستخراج بنجاح\nالنقاط: {user_data['points']} | المحاولات المتبقية: {DAILY_LIMIT - get_user(user.id)['usage_count']}"
            )
        
        # تنظيف الملفات المؤقتة
        try:
            temp_dir = os.path.dirname(zip_path)
            for filename in os.listdir(temp_dir):
                file_path = os.path.join(temp_dir, filename)
                os.remove(file_path)
            os.rmdir(temp_dir)
        except Exception as clean_err:
            logger.error(f"Error cleaning temp files: {clean_err}")
            
    except Exception as e:
        await msg.edit_text(f"❌ حدث خطأ: {str(e)}")

# ------------------- نظام إدارة القنوات -------------------
async def admin_channels_menu(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT channel_id, username, title FROM channels")
    channels = cursor.fetchall()
    conn.close()
    
    keyboard = [
        [InlineKeyboardButton("➕ إضافة قناة", callback_data=CALLBACK_ADD_CHANNEL)],
        [InlineKeyboardButton("➖ حذف قناة", callback_data=CALLBACK_REMOVE_CHANNEL)],
        [InlineKeyboardButton("🔙 رجوع", callback_data="admin_panel")]
    ]
    
    text = "🛠 إدارة القنوات:\n\nالقنوات المسجلة:\n"
    if channels:
        for channel in channels:
            text += f"- {channel[2]} (@{channel[1]}) - ID: {channel[0]}\n"
    else:
        text += "لا توجد قنوات مسجلة"
    
    await query.edit_message_text(
        text,
        reply_markup=InlineKeyboardMarkup(keyboard)
    )

async def add_channel_prompt(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    
    await query.edit_message_text(
        "📌 أرسل معرف القناة أو رابطها الآن (مثال: @channelname أو https://t.me/channelname)",
        reply_markup=InlineKeyboardMarkup([
            [InlineKeyboardButton("🔙 رجوع", callback_data=CALLBACK_ADMIN_CHANNELS)]
        ])
    )
    context.user_data['awaiting_channel'] = True

async def handle_channel_input(update: Update, context: ContextTypes.DEFAULT_TYPE):
    if 'awaiting_channel' in context.user_data:
        channel_input = update.message.text.strip()
        
        # استخراج معرف القناة من المدخلات
        if channel_input.startswith("https://t.me/"):
            channel_username = channel_input.split("/")[-1]
        elif channel_input.startswith("@"):
            channel_username = channel_input[1:]
        else:
            channel_username = channel_input
        
        # هنا يجب إضافة التحقق من أن البوت مشرف في القناة
        # لكن هذا يتطلب واجهة برمجة تطبيقات خاصة
        
        try:
            # الحصول على معلومات القناة
            chat = await context.bot.get_chat(f"@{channel_username}")
            
            conn = get_db_connection()
            cursor = conn.cursor()
            cursor.execute('''
                INSERT OR REPLACE INTO channels 
                (channel_id, username, title, added_by, add_date)
                VALUES (?, ?, ?, ?, ?)
            ''', (chat.id, chat.username, chat.title, update.effective_user.id, datetime.now().isoformat()))
            conn.commit()
            conn.close()
            
            await update.message.reply_text(
                f"✅ تمت إضافة القناة {chat.title} (@{chat.username}) بنجاح",
                reply_markup=InlineKeyboardMarkup([
                    [InlineKeyboardButton("العودة لإدارة القنوات", callback_data=CALLBACK_ADMIN_CHANNELS)]
                ])
            )
        except Exception as e:
            await update.message.reply_text(f"❌ خطأ في إضافة القناة: {str(e)}")
        
        del context.user_data['awaiting_channel']

async def remove_channel_menu(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT channel_id, username, title FROM channels")
    channels = cursor.fetchall()
    conn.close()
    
    if not channels:
        await query.edit_message_text(
            "⚠️ لا توجد قنوات مسجلة للحذف",
            reply_markup=InlineKeyboardMarkup([
                [InlineKeyboardButton("🔙 رجوع", callback_data=CALLBACK_ADMIN_CHANNELS)]
            ])
        )
        return
    
    keyboard = []
    for channel in channels:
        keyboard.append([
            InlineKeyboardButton(
                f"حذف {channel[2]}",
                callback_data=f"remove_channel_{channel[0]}"
            )
        ])
    
    keyboard.append([InlineKeyboardButton("🔙 رجوع", callback_data=CALLBACK_ADMIN_CHANNELS)])
    
    await query.edit_message_text(
        "اختر القناة التي تريد حذفها:",
        reply_markup=InlineKeyboardMarkup(keyboard)
    )

async def remove_channel(update: Update, context: ContextTypes.DEFAULT_TYPE, channel_id: int):
    query = update.callback_query
    await query.answer()
    
    conn = get_db_connection()
    cursor = conn.cursor()
    
    try:
        # الحصول على معلومات القناة قبل الحذف
        cursor.execute("SELECT username, title FROM channels WHERE channel_id = ?", (channel_id,))
        channel = cursor.fetchone()
        
        if not channel:
            await query.edit_message_text("❌ القناة غير موجودة")
            return
        
        # حذف القناة
        cursor.execute("DELETE FROM channels WHERE channel_id = ?", (channel_id,))
        conn.commit()
        
        await query.edit_message_text(
            f"✅ تم حذف القناة {channel[1]} (@{channel[0]}) بنجاح",
            reply_markup=InlineKeyboardMarkup([
                [InlineKeyboardButton("العودة لإدارة القنوات", callback_data=CALLBACK_ADMIN_CHANNELS)]
            ])
        )
    except Exception as e:
        await query.edit_message_text(f"❌ خطأ في حذف القناة: {str(e)}")
    finally:
        conn.close()

# ------------------- نظام الإذاعة -------------------
async def admin_broadcast_menu(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT COUNT(*) FROM users WHERE is_banned = 0")
    active_users = cursor.fetchone()[0]
    conn.close()
    
    keyboard = [
        [InlineKeyboardButton("📢 إرسال إذاعة", callback_data="start_broadcast")],
        [InlineKeyboardButton("🔙 رجوع", callback_data="admin_panel")]
    ]
    
    text = f"""
📢 نظام الإذاعة:

عدد المستخدمين النشطين: {active_users}

يمكنك إرسال رسالة إلى جميع المستخدمين النشطين (غير المحظورين) في البوت.
"""
    await query.edit_message_text(
        text,
        reply_markup=InlineKeyboardMarkup(keyboard)
    )

async def start_broadcast(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    
    await query.edit_message_text(
        "📝 أرسل الرسالة التي تريد إذاعتها الآن (يمكن أن تكون نص، صورة، فيديو، الخ)",
        reply_markup=InlineKeyboardMarkup([
            [InlineKeyboardButton("إلغاء", callback_data=CALLBACK_CANCEL_BROADCAST)]
        ])
    )
    context.user_data['broadcasting'] = True

async def send_broadcast_message(update: Update, context: ContextTypes.DEFAULT_TYPE):
    if 'broadcasting' in context.user_data:
        message = update.message
        
        # حفظ الرسالة في context للإستخدام لاحقاً
        context.user_data['broadcast_message'] = message
        
        keyboard = [
            [InlineKeyboardButton("✅ تأكيد الإرسال", callback_data=CALLBACK_CONFIRM_BROADCAST)],
            [InlineKeyboardButton("❌ إلغاء", callback_data=CALLBACK_CANCEL_BROADCAST)]
        ]
        
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("SELECT COUNT(*) FROM users WHERE is_banned = 0")
        active_users = cursor.fetchone()[0]
        conn.close()
        
        await message.reply_text(
            f"⚠️ هل أنت متأكد من أنك تريد إرسال هذه الرسالة إلى {active_users} مستخدم؟\n\n"
            "محتوى الرسالة:\n" + (message.text or message.caption or "ملف مرفق"),
            reply_markup=InlineKeyboardMarkup(keyboard)
        )

async def send_broadcast(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    
    if 'broadcast_message' not in context.user_data:
        await query.edit_message_text("❌ لم يتم العثور على رسالة للإذاعة")
        return
    
    message = context.user_data['broadcast_message']
    await query.edit_message_text("🔄 جاري إرسال الرسالة إلى جميع المستخدمين...")
    
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT user_id FROM users WHERE is_banned = 0")
    users = cursor.fetchall()
    conn.close()
    
    success = 0
    failed = 0
    
    for user in users:
        try:
            if message.text:
                await context.bot.send_message(
                    chat_id=user[0],
                    text=message.text
                )
            elif message.photo:
                await context.bot.send_photo(
                    chat_id=user[0],
                    photo=message.photo[-1].file_id,
                    caption=message.caption
                )
            elif message.video:
                await context.bot.send_video(
                    chat_id=user[0],
                    video=message.video.file_id,
                    caption=message.caption
                )
            elif message.document:
                await context.bot.send_document(
                    chat_id=user[0],
                    document=message.document.file_id,
                    caption=message.caption
                )
            success += 1
        except Exception as e:
            logger.error(f"Failed to send broadcast to {user[0]}: {e}")
            failed += 1
    
    # تسجيل الإذاعة في قاعدة البيانات
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute('''
        INSERT INTO broadcasts 
        (admin_id, message, sent_date, users_count)
        VALUES (?, ?, ?, ?)
    ''', (
        update.effective_user.id,
        message.text or message.caption or "ملف مرفق",
        datetime.now().isoformat(),
        success
    ))
    conn.commit()
    conn.close()
    
    # تنظيف البيانات المؤقتة
    del context.user_data['broadcasting']
    del context.user_data['broadcast_message']
    
    await query.edit_message_text(
        f"✅ تم إرسال الرسالة بنجاح\n\n"
        f"✅ تمت بنجاح: {success}\n"
        f"❌ فشل في الإرسال: {failed}",
        reply_markup=InlineKeyboardMarkup([
            [InlineKeyboardButton("العودة للوحة التحكم", callback_data="admin_panel")]
        ])
    )

async def cancel_broadcast(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    
    if 'broadcasting' in context.user_data:
        del context.user_data['broadcasting']
    if 'broadcast_message' in context.user_data:
        del context.user_data['broadcast_message']
    
    await query.edit_message_text(
        "❌ تم إلغاء عملية الإذاعة",
        reply_markup=InlineKeyboardMarkup([
            [InlineKeyboardButton("العودة للوحة التحكم", callback_data="admin_panel")]
        ])
    )

# ------------------- أوامر الأدمن -------------------
async def admin_panel(update: Update, context: ContextTypes.DEFAULT_TYPE):
    if update.effective_user.id != ADMIN_ID:
        await update.message.reply_text("⚠️ هذا الأمر للمشرف فقط!")
        return
    
    keyboard = [
        [InlineKeyboardButton("📊 الإحصائيات", callback_data=CALLBACK_ADMIN_STATS)],
        [InlineKeyboardButton("📢 إذاعة", callback_data=CALLBACK_ADMIN_BROADCAST)],
        [InlineKeyboardButton("🛠 إدارة القنوات", callback_data=CALLBACK_ADMIN_CHANNELS)],
        [InlineKeyboardButton("👤 إدارة المستخدمين", callback_data=CALLBACK_ADMIN_USERS)]
    ]
    
    await update.message.reply_text(
        "👨‍💻 لوحة التحكم الإدارية:",
        reply_markup=InlineKeyboardMarkup(keyboard)
    )

async def admin_stats(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    
    conn = get_db_connection()
    cursor = conn.cursor()
    
    # إحصائيات المستخدمين
    cursor.execute("SELECT COUNT(*) FROM users")
    total_users = cursor.fetchone()[0]
    
    cursor.execute("SELECT COUNT(*) FROM users WHERE is_banned = 1")
    banned_users = cursor.fetchone()[0]
    
    cursor.execute("SELECT COUNT(*) FROM users WHERE date(join_date) = date('now')")
    new_today = cursor.fetchone()[0]
    
    # إحصائيات الاستخدام
    cursor.execute("SELECT SUM(usage_count) FROM users")
    total_usage = cursor.fetchone()[0] or 0
    
    cursor.execute("SELECT COUNT(*) FROM referrals")
    total_referrals = cursor.fetchone()[0]
    
    # إحصائيات القنوات
    cursor.execute("SELECT COUNT(*) FROM channels")
    total_channels = cursor.fetchone()[0]
    
    # إحصائيات البث
    cursor.execute("SELECT COUNT(*) FROM broadcasts")
    total_broadcasts = cursor.fetchone()[0]
    
    conn.close()
    
    keyboard = [
        [InlineKeyboardButton("👤 إدارة المستخدمين", callback_data=CALLBACK_ADMIN_USERS)],
        [InlineKeyboardButton("🔙 رجوع", callback_data="admin_panel")]
    ]
    
    text = f"""
📊 إحصائيات البوت:

👥 المستخدمون:
- الإجمالي: {total_users}
- المحظورون: {banned_users}
- الجدد اليوم: {new_today}

📈 الاستخدام:
- إجمالي المحاولات: {total_usage}
- إجمالي الإحالات: {total_referrals}

📢 الإذاعة:
- عدد الإذاعات: {total_broadcasts}

🛠 القنوات:
- عدد القنوات: {total_channels}
"""
    await query.edit_message_text(
        text,
        reply_markup=InlineKeyboardMarkup(keyboard)
    )

async def admin_users_menu(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT user_id, username, first_name, is_banned FROM users ORDER BY join_date DESC LIMIT 10")
    users = cursor.fetchall()
    conn.close()
    
    text = "👥 آخر 10 مستخدمين:\n"
    keyboard = []
    
    for user in users:
        status = "⛔ محظور" if user[3] else "✅ نشط"
        text += f"\n- {user[0]} {user[2]} (@{user[1]}) - {status}"
        
        # أزرار الحظر/فك الحظر
        if user[3]:  # إذا كان محظوراً
            keyboard.append([InlineKeyboardButton(
                f"فك حظر {user[0]}",
                callback_data=f"{CALLBACK_UNBAN_USER}_{user[0]}"
            )])
        else:
            keyboard.append([InlineKeyboardButton(
                f"حظر {user[0]}",
                callback_data=f"{CALLBACK_BAN_USER}_{user[0]}"
            )])
    
    keyboard.append([InlineKeyboardButton("🔙 رجوع", callback_data=CALLBACK_ADMIN_STATS)])
    
    await query.edit_message_text(
        text,
        reply_markup=InlineKeyboardMarkup(keyboard)
    )

# ------------------- معالجة الأخطاء -------------------
async def error_handler(update: Update, context: ContextTypes.DEFAULT_TYPE):
    """هاندلر للأخطاء العامة"""
    logger.error(f"حدث خطأ: {context.error}", exc_info=context.error)
    
    if update and update.effective_message:
        try:
            await update.effective_message.reply_text(
                "⚠️ حدث خطأ غير متوقع. الرجاء المحاولة لاحقاً."
            )
        except Exception as e:
            logger.error(f"Error in error handler while sending message: {e}")

# ------------------- تشغيل البوت -------------------
def main():
    app = ApplicationBuilder().token(BOT_TOKEN).build()
    
    # الأوامر الأساسية
    app.add_handler(CommandHandler("start", start))
    app.add_handler(CommandHandler("admin", admin_panel))
    app.add_handler(CommandHandler("ban", ban_user_cmd))
    app.add_handler(CommandHandler("unban", unban_user_cmd))
    
    # معالجة الرسائل
    app.add_handler(MessageHandler(filters.TEXT & ~filters.COMMAND, handle_scrape))
    app.add_handler(MessageHandler(filters.ALL & ~filters.COMMAND, handle_channel_input))
    app.add_handler(MessageHandler(filters.ALL & ~filters.COMMAND, send_broadcast_message))
    
    # معالجة الأزرار
    app.add_handler(CallbackQueryHandler(button_handler))
    
    # معالجة الأخطاء
    app.add_error_handler(error_handler)
    
    logger.info("🤖 البوت يعمل الآن...")
    app.run_polling()

if __name__ == '__main__':
    main()