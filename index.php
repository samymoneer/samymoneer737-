<?php
ob_start();
error_reporting(0);
define("API_KEY",'8001602131:AAHu4tNX4laV0SEVfDnpLsXxphuUv8mjaes'); // توكن البوت هنا

$botname = bot('getme', ['bot'])->result->username;
$channel1 = "@python1yemen"; // القناة الأولى
$channel2 = "@python2yemen"; // القناة الثانية
$Dev = "@C_CA7"; // يوزر المطور
$Ch = "@python1yemen"; // يوزر قناتك

function bot($method, $datas = []) {
    $url = "https://api.telegram.org/bot" . API_KEY . "/$method";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    if (curl_error($ch)) {
        var_dump(curl_error($ch));
    } else {
        return json_decode($res);
    }
}

$update = json_decode(file_get_contents('php://input'));
$message = $update->message;
$id = $message->from->id;
$chat_id = $message->chat->id;
$text = $message->text;
$user = $message->from->username;
$name = $message->from->first_name;
$from_id = $message->from->id;

if (isset($update->callback_query)) {
    $chat_id = $update->callback_query->message->chat->id;
    $message_id = $update->callback_query->message->message_id;
    $data = $update->callback_query->data;
    $user = $update->callback_query->from->username;
}

// دالة التحقق من الاشتراك
function isSubscribed($user_id) {
    global $channel1, $channel2;
    $check1 = bot('getChatMember', ['chat_id' => $channel1, 'user_id' => $user_id])->result->status;
    $check2 = bot('getChatMember', ['chat_id' => $channel2, 'user_id' => $user_id])->result->status;
    return ($check1 == 'member' || $check1 == 'creator' || $check1 == 'administrator') && 
           ($check2 == 'member' || $check2 == 'creator' || $check2 == 'administrator');
}

// دالة إرسال رسالة الاشتراك الإجباري
function sendSubscribeMessage($chat_id) {
    global $channel1, $channel2;
    bot('sendmessage', [
        'chat_id' => $chat_id,
        'text' => "⚠️ *عذراً عزيزي*\n\n🔹 لتتمكن من استخدام البوت يجب عليك الاشتراك في القنوات التالية:\n\n" . 
                 "1. $channel1\n2. $channel2\n\n" .
                 "بعد الاشتراك اضغط /start مرة أخرى",
        'parse_mode' => 'markdown',
        'disable_web_page_preview' => true,
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text' => "القناة الأولى", 'url' => "https://t.me/python1yemen"]],
                [['text' => "القناة الثانية", 'url' => "https://t.me/python2yemen"]],
                [['text' => "تـحـقـق مـن الاشـتـراك", 'callback_data' => "check_sub"]]
            ]
        ])
    ]);
}

// معالجة زر التحقق من الاشتراك
if (isset($data) && $data == "check_sub") {
    if (isSubscribed($from_id)) {
        bot('answerCallbackQuery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "✔️ تم الاشتراك بنجاح، يمكنك استخدام البوت الآن",
            'show_alert' => true
        ]);
        bot('sendmessage', [
            'chat_id' => $chat_id,
            'text' => "*مرحباً بك!*\n\nيمكنك الآن استخدام البوت، أرسل /start للبدء",
            'parse_mode' => 'markdown'
        ]);
    } else {
        bot('answerCallbackQuery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "❌ لم تشترك في جميع القنوات بعد",
            'show_alert' => true
        ]);
    }
}

// دالة تاريخ الإنشاء
function cdate($dd) { 
    $h = ['x-api-key: e758fb28-79be-4d1c-af6b-066633ded128']; 
    $d = ["telegramId" => (int)$dd]; 
    $ch = curl_init('https://restore-access.indream.app/regdate'); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, $h); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($d)); 
    $r = curl_exec($ch); 
    curl_close($ch);
    return json_decode($r)->data->date; 
} 

// دالة معلومات الدردشة
function get_chat_info($id) {
    global $chat_id;
    $response = json_decode(file_get_contents("https://api.telegram.org/bot".API_KEY."/getChat?chat_id=$id"), true);
    if ($response['ok']) {
        $result = $response['result'];
        $members_count = json_decode(file_get_contents("https://api.telegram.org/bot".API_KEY."/getChatMembersCount?chat_id=$id"), true);
        $is_public = isset($result['username']) ? 'عامة' : 'خاصة';
        return [
            'type' => $result['type'],
            'title' => $result['title'] ?? $result['first_name'] . ' ' . ($result['last_name'] ?? ''),
            'username' => $result['username'] ?? 'N/A',
            'members_count' => $members_count['result'] ?? 'N/A',
            'is_public' => $is_public,
        ];
    }
    return null;
}

// معالجة الأمر /start
if ($text == "/start") {
    if (!isSubscribed($from_id)) {
        sendSubscribeMessage($chat_id);
        exit;
    }
    
    bot('Sendmessage', [
        'chat_id' => $chat_id,
        'text' => "*• مرحبا بك 👤. 
• في بوت معرفه انشاء حسابك 📅. 
• فقط قم بارسال ايدي ←⦗ حسابك - قناتك - مجموعتك ⦘ 
• وساقوم باضهار تاريخ الانشاء مع معلومات الايدي كامله*
```• مثال ←⦗ 1427981991 ⦘```",
        'parse_mode' => "markdown",
        'disable_web_page_preview' => true,
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text' => "⦗ مطور البوت ⦘", 'url' => "https://t.me/$Dev"], ['text' => "⦗ قناة البوت ⦘", 'url' => "https://t.me/$Ch"]],
                [['text' => "القناة الأولى", 'url' => "https://t.me/python1yemen"], ['text' => "القناة الثانية", 'url' => "https://t.me/python2yemen"]]
            ]
        ])
    ]);
}

// معالجة الأيدي المرسل
if (is_numeric($text)) {
    if (!isSubscribed($from_id)) {
        sendSubscribeMessage($chat_id);
        exit;
    }
    
    $chat_info = get_chat_info($text);
    if ($chat_info) {
        $date = cdate($text);
        $type = $chat_info['type'] == 'private' ? 'شخصية' : ($chat_info['type'] == 'group' || $chat_info['type'] == 'supergroup' ? 'مجموعة' : 'قناة');
        $response_text = "• تم الكشف عن المعلومات بنجاح 🤍. \n\n";
        $response_text .= "• النوع ← $type\n";
        $response_text .= "• الاسم ← " . $chat_info['title'] . "\n";
        $response_text .= "• اليوزر ← @" . $chat_info['username'] . "\n";
        $response_text .= "• الحساب ← " . $chat_info['is_public'] . "\n";
        if ($chat_info['members_count'] != 'N/A' && ($type == 'مجموعة' || $type == 'قناة')) {
            $response_text .= "• عدد الاعضاء ← " . $chat_info['members_count'] . "\n";
        }
        $response_text .= "• تاريخ الإنشاء ← $date";

        bot('sendmessage', [
            'chat_id' => $chat_id,
            'reply_to_message_id' => $message->message_id, 
            'text' => $response_text,
            'parse_mode' => "markdown",
            'disable_web_page_preview' => true,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => "⦗ مطور البوت ⦘", 'url' => "https://t.me/$Dev"], ['text' => "⦗ قناة المطور ⦘", 'url' => "https://t.me/$Ch"]],
                    [['text' => "القناة الأولى", 'url' => "https://t.me/python1yemen"], ['text' => "القناة الثانية", 'url' => "https://t.me/python2yemen"]]
                ]
            ])
        ]);
    } else {
        bot('sendmessage', [
            'chat_id' => $chat_id,
            'reply_to_message_id' => $message->message_id,
            'text' => "لم يتم العثور على معلومات الحساب. تأكد من صحة المعرف المرسل.",
        ]);
    }
}

// ميزة جديدة: معلومات المستخدم
if ($text == "/info") {
    if (!isSubscribed($from_id)) {
        sendSubscribeMessage($chat_id);
        exit;
    }
    
    $user_info = bot('getChat', ['chat_id' => $from_id])->result;
    $profile_photos = bot('getUserProfilePhotos', ['user_id' => $from_id, 'limit' => 1])->result;
    $photo_id = $profile_photos->photos[0][2]->file_id ?? null;
    
    $info_text = "📌 *معلومات حسابك:*\n\n";
    $info_text .= "🆔 *الأيدي:* `$from_id`\n";
    $info_text .= "👤 *الاسم:* " . ($user_info->first_name ?? '') . " " . ($user_info->last_name ?? '') . "\n";
    $info_text .= "📛 *اليوزر:* @" . ($user_info->username ?? 'لا يوجد') . "\n";
    $info_text .= "📅 *تاريخ الانضمام لتليجرام:* " . cdate($from_id) . "\n";
    
    if ($photo_id) {
        bot('sendPhoto', [
            'chat_id' => $chat_id,
            'photo' => $photo_id,
            'caption' => $info_text,
            'parse_mode' => 'markdown'
        ]);
    } else {
        bot('sendmessage', [
            'chat_id' => $chat_id,
            'text' => $info_text,
            'parse_mode' => 'markdown'
        ]);
    }
}

unlink("error_log");