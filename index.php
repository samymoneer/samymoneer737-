<?php
ob_start();
mkdir('data');
mkdir('data/id');
mkdir('data/txt');
$API_KEY= '7551504466:AAGpy3-hZKM0ZTDky5KYtJMPgprdwsJz6h0';
define('API_KEY',$API_KEY);
echo file_get_contents("https://api.telegram.org/bot" . API_KEY . "/setwebhook?url=" . $_SERVER['SERVER_NAME'] . "" . $_SERVER['SCRIPT_NAME']);
function bot($method,$datas=[]){
$amrakl = http_build_query($datas);
$url = "https://api.telegram.org/bot".API_KEY."/".$method."?$amrakl";
$amrakl = file_get_contents($url);
return json_decode($amrakl);
}

$update = json_decode(file_get_contents('php://input'));
$message = $update->message;
$chat_id = $message->chat->id;
$text = $message->text;
$message_id = $message->message_id;
$id = $message->from->id;
if($update->callback_query){
$id                                   = $update->callback_query->message->chat->id;
}else{
$id           						= $update->message->chat->id;
}
if(isset($update->callback_query)){
$chat_id = $update->callback_query->message->chat->id;
$message_id = $update->callback_query->message->message_id;
$data = $update->callback_query->data;
$user = $update->callback_query->from->username;
$first = $update->callback_query->from->first_name;
}


#=================={        مهم.        }================#
#=================={بيانات حسابك والقنوات}================#
#====================================================#
#====================================================#
#====================================================#
#====================================================#
$me = bot('getme',['bot'])->result->username;
$bot="GG";// لازم تحط اسم مجلد بوتك الي فيه ملفات البوت مهم
$bot_name="GG PHONE";// حط اسم بوتك الي بيضهر للمستخدمين 
$profit_price_sale = 20; // هنا حدد نسبة الربح عندما تبيع للمستخدمين
$Free=0.001;//نسبة ربح رابط الدعوة
#=========={القنوات الادمن وبيانات حسابك في TG-Lion}=========#
$apiKay_Lion= "nMmim07nyz9baDk4Cc"; # هنا ضع مفتاح api الخاص بك في TG-Lion
$Your_ID=7558300112; # هنا ضع ايدي حسابك في TG-Lion
$sim =-1002347463821; #هنا ضع القناة الاشتراك الاجباري
$PAY =-1002609244169; # هنا ضع الارقام المكتملة الناجحه الخاصة بالإدارة
$activation=-1002609093313; # هنا ضع قناة التفعيلات
$tele =-1002555864273; # هنا ضع قناة تخزين ارقام تلي
$buy_out =-1002515723864; # هنا ضع عمليات تسجيل الخروج والشراء الخاص بالادارة
$system  =-1002520487481; # هنا ضع اشعارات الدخول والتحويلات اشعارات النظام
#====================================================#
#====================================================#
#====================================================#
#====================================================#
#====================================================#
#====================================================#
#====================================================#
#====================================================#




#=========={التخزينات}==========#
function Numbers($array){
file_put_contents('data/number.json', json_encode($array,64|128|256));
}
// مسارات ووضائف اخرى
$step = file_get_contents("data/id/$id/step.txt");
$exstep=explode("|", $step);
$extext = explode("\n", $text);
$ex_text=explode(" ", $text);
$exdata=explode("-", $data);
$tele_number = json_decode(file_get_contents('data/number.json'),true);
$mr = json_decode(file_get_contents("ID/$chat_id/$points.txt"),true);
$Balance = file_get_contents("ID/$chat_id/points.txt"); #رصيد العضو#
if($Balance==null){
$Balance=0;
}
if(!is_dir("data/id/$id")){
mkdir("data/id/$id");
}
if(!is_dir("ID/$chat_id")){
mkdir("ID/$chat_id");
file_put_contents("ID/$chat_id/points.txt", 0);
bot('sendMessage',[
'chat_id'=>$system,
'text'=>"
تم دخول عميل جديد ✅
- العميل: $first
- الايدي: `$chat_id`
- يوزرة: $user
",
'parse_mode'=>"MarkDown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"تواصل مع العميل",'url'=>"tg://openmessage?user_id=$id"]]
]
])
]);
$chal=file_get_contents("data/id/$id/lift.txt");
if($chal !="close" and $chal != $id){
$cc = $ex_text[1]; 
file_put_contents("data/id/$id/lift.txt", $cc);
}
}

#=========={الإشتراك الإجباري}==========#
$status = bot('getChatMember',['chat_id'=>$sim,'user_id'=>$chat_id])->result->status;
if($data == null and $status == 'left'){
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"
⚠️ عزيزي المستخدم يجب عليك الإشتراك في قناة البوت لتتمكن من معرفة كل جديد
",
'reply_to_message_id'=>$message_id,
'parse_mode'=>"html",
'reply_markup'=>json_encode([ 
'inline_keyboard'=>[ 
[['text'=>"قناة التفعيلات",'url'=>"https://t.me/TG_LionAPI"]]
]
])
]);
exit;
}
if($data != null and $status == 'left'){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
⚠️ عزيزي المستخدم يجب عليك الإشتراك في قناة البوت لتتمكن من معرفة كل جديد
",
'reply_to_message_id'=>$message_id,
'parse_mode'=>"html",
'reply_markup'=>json_encode([ 
'inline_keyboard'=>[ 
[['text'=>"قناة التفعيلات",'url'=>"https://t.me/TG_LionAPI"]]
]
])
]);
exit;
}

#=========={القائمة الرئيسيه}==========#1
// قائمة رئيسية 1
if($ex_text[0] == '/start'){
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"
🏡: مرحبا بكم في بوت $bot_name

- ايدي حسابك: `$id`
- رصيد حسابك: $$Balance
- مستوى حسابك: VIP1

💻 تحكم بالبوت عبر الأزرار في الأسفل:
",
'parse_mode' => "MarkDown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => 'شراء حسابات تلجرام جاهزه', 'callback_data' => "Buyxvx"]], 
[['text' => 'رصيد مجانا', 'callback_data' => "assignment"], ['text' => "شحن رصيدك", 'url' => "tg://user?id=$Your_ID"]],
[['text' => 'حسابي', 'callback_data' => "myaca"], ['text' => "قناة التفعيلات", 'url' => "https://t.me/TG_LionAPI"]],
[['text' => 'تحويل رصيد', 'callback_data' => "SendCoin"]]
]
])
]);
unlink("data/id/$id/step.txt");
$chal=file_get_contents("data/id/$id/lift.txt");
if($chal != null and $chal != $chat_id and $chal !="close"){
bot('sendMessage',[
'chat_id'=>$chal,
'text'=>"
• قام شخص جديد باستخدام رابط إحالتك  
• ولقد ربحت $Free دولار
",
'parse_mode'=>"MarkDown",
]);
$points = file_get_contents("ID/$chal/points.txt");
$aa = $points + $Free;
file_put_contents("ID/$chal/points.txt",$aa);
file_put_contents("data/id/$id/lift.txt","close");// علشان تضمن مايقدر يربح مرة ثاني
}
exit;
}
#=========={القائمة الرئيسيه}==========#2
if($text == '/start'){
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"
🏡: مرحبا بكم في بوت $bot_name

- ايدي حسابك: `$id`
- رصيد حسابك: $$Balance
- مستوى حسابك: VIP1

💻 تحكم بالبوت عبر الأزرار في الأسفل:
",
'parse_mode' => "MarkDown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => 'شراء حسابات تلجرام جاهزه', 'callback_data' => "Buyxvx"]], 
[['text' => 'رصيد مجانا', 'callback_data' => "assignment"], ['text' => "شحن رصيدك", 'url' => "tg://user?id=$Your_ID"]],
[['text' => 'حسابي', 'callback_data' => "myaca"], ['text' => "قناة التفعيلات", 'url' => "https://t.me/TG_LionAPI"]],
[['text' => 'تحويل رصيد', 'callback_data' => "SendCoin"]]
]
])
]);
unlink("data/id/$id/step.txt");
exit;
}
#=========={القائمة الرئيسية}==========#3
if($data == "back"){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
🏡: مرحبا بكم في بوت $bot_name

- ايدي حسابك: `$id`
- رصيد حسابك: $$Balance دولار
- مستوى حسابك: VIP1

💻 تحكم بالبوت عبر الأزرار في الأسفل:
",
'parse_mode' => "MarkDown",
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => 'شراء حسابات تلجرام جاهزه', 'callback_data' => "Buyxvx"]], 
[['text' => 'رصيد مجانا', 'callback_data' => "assignment"], ['text' => "شحن رصيدك", 'url' => "tg://user?id=$Your_ID"]],
[['text' => 'حسابي', 'callback_data' => "myaca"], ['text' => "قناة التفعيلات", 'url' => "https://t.me/TG_LionAPI"]],
[['text' => 'تحويل رصيد', 'callback_data' => "SendCoin"]]
]
])
]);
unlink("data/id/$id/step.txt");
}

if($exdata[0] == "YSg" or $exdata[0] == "YSb"){
if($exdata[1] > $Balance or $Balance < $exdata[1]){
bot('answercallbackquery',[
'callback_query_id' => $update->callback_query->id,
'text'=>"- رصيدك غير كافي رصيدك الحالي $Balance دولار",
'show_alert'=>false,
]);
unlink("data/id/$id/step.txt");
exit;
}
}
#=========={الإحالات}===========#
if($data == "assignment"){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
شارك رابط الدعوة الخاص بك مع أصدقائك او قنواتك او اي مكان واحصل على $assignru دولار مجاناً لكل شخص يقوم بالدخول عبر رابطك تربح $$Free

https://t.me/$me?start=$id
",
'parse_mode'=>"html",
'disable_web_page_preview'=>true,
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'رجوع','callback_data'=>'backk']]
]
])
]);
unlink("data/id/$id/step.txt");
}

#=========={تحويل دولار لمستخدم اخر}=========#
if($data == "SendCoin"){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
قم بإرسال ID العميل الذي تريد تحويل الاموال الية
",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'رجوع','callback_data'=>'back']]
]
])
]);
file_put_contents("data/id/$id/step.txt","se");
}
if($text !== '/start' and $text !== null and $step == 'se'){
$idEM = $text;
$ttt = json_decode(file_get_contents("ID/$idEM/points.txt"),true);
if (!isset($ttt)) {
bot('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"
- عذرا هاذا المستخدم غير موجود في الروبوت
",
'parse_mode'=>"html",
'reply_to_message_id'=>$message_id,
]);
exit;
}elseif($idEM == $id){
bot('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"
- عذرا عزيزي العميل لايمكنك التحويل لنفس لحسابك
",
'parse_mode'=>"html",
'reply_to_message_id'=>$message_id,
]);
}else{
bot('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"
- إرسل عدد الدولارات اللتي تريد تحويلها لهذا المستخدم
",
'reply_to_message_id'=>$message_id,
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'رجوع','callback_data'=>'SendCoin']]
]
])
]);
file_put_contents("data/id/$id/step.txt","ce|$text");
exit;
}
}
if($text !== '/start' and $text !== null and $exstep[0] == 'ce'){
$idEM=$exstep[1];
$price = $text;
if($price > $Balance){
bot('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"
- رصيدك غير كافي 
- رصيدك الحالي $Balance 
",
'reply_to_message_id'=>$message_id,
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'رجوع','callback_data'=>'SendCoin']]
]
])
]);
unlink("data/id/$id/step.txt");
exit;
}elseif(0.01 > $price){
bot('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"
- عذرا الحد الادنى 0.01 دولار
",
'reply_to_message_id'=>$message_id,
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'رجوع','callback_data'=>'SendCoin']]
]
])
]);
unlink("data/id/$id/step.txt");
exit;
}elseif($price <= $Balance){
bot('sendmessage',[
'chat_id'=>$chat_id,
'text'=>" 
- هل أنت متأكد من شحن $ $price دولار: 
- ايدي المستخدم: $idEM 
",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'نعم','callback_data'=>"YSb-$price-$idEM"]],
[['text'=>'رجوع','callback_data'=>'SendCoin']]
]
])
]);
unlink("data/id/$id/step.txt");
exit;
}else{
bot('sendmessage',[
'chat_id'=>$chat_id,
'text'=>"
- رصيدك غير كافي 
- رصيدك الحالي $Balance دولار
",
'parse_mode'=>"MarkDown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'رجوع','callback_data'=>'back']]
]
])
]);
unlink("data/id/$id/step.txt");
exit;
}
}
if($exdata[0] == "YSb"){
$price = $exdata[1];
$idEM = $exdata[2];
$sendbot2=$sendbot+1;
$ms = file_get_contents("ID/$idEM/points.txt");// مسار رصيد المستلم
$mr = file_get_contents("ID/$id/points.txt");// مسار رصيد المرسل
$msp = $ms + $price;
$mrp = $mr-$price;
if($price == null){
exit;
}else{
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
- تم خصم $$price من رصيدك وتم تحويلها إلى $idEM ✅

- عمولة التحويل: 0$
- رصيدك المتبقي: $mrp دولار
",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'رجوع','callback_data'=>'back']]
]
])
]);
bot('sendmessage',[
'chat_id'=>$idEM,
'text'=>"
- تم شحن  $price دولار الى حسابك ✅.
- تم الشحن من  $id
- رصيدك الحالي  $msp دولار
",
'disable_web_page_preview'=>true,
'parse_mode'=>"MarkDown",
]);
bot('sendmessage',[
'chat_id'=>$system,
'text'=>"
⚜️ عملية تحويل دولار بين مستخدمين:

🙈 - المرسل: $id
🙈 - المستلم: $idEM
💰- عدد الدولارات : $price
🤖 - رسوم التحويل : $0
🏧 - تاريخ : date('Y-m-d H:i:s')
➖ - رصيد المرسل بعد التحويل : $mrp
➖ - رصيد المستلم بعد التحويل : $msp
",
'parse_mode'=>"MarkDown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"المرسل",'url'=>"tg://openmessage?user_id=$id"]],
[['text'=>"المستلم",'url'=>"tg://openmessage?user_id=$idEM"]]
]
])
]);
$ms = file_get_contents("ID/$idEM/points.txt");// مسار رصيد المستلم
$mr = file_get_contents("ID/$id/points.txt");// مسار رصيد المرسل
$ok = $mr - $price;
file_put_contents("ID/$chat_id/points.txt",$ok);
$ok = $ms + $price;
file_put_contents("ID/$idEM/points.txt",$ok);
unlink("data/id/$id/step.txt");
}
}

# قسم بيانات حساب المستخدم
if($data == "myaca"){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
✅:مرحبا هذه معلومات وبيانات حسابك.

- أيدي حسابك: $id
- رصيد الحساب: $Balance دولار
",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'رجوع','callback_data'=>'back']]
]
])
]);
unlink("data/id/$id/step.txt");
exit;
}

#=========={قائمة شراء حساب}==========#1
if($data=="Buyxvx"){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
💚 شراء حسابات Telegram جاهزة

➖ يمكنك شراء حساب تلجرام بضغطة زر
➖ يمكنك طلب عدة اكواد للحساب مجانا •
➖ رصيد حسابك: $Balance دولار •

",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'السيـرفر 1','callback_data'=>"Buynumtele2-1"]],
[['text'=>'السيـرفر 2','callback_data'=>"Buynumtele2-2"]],
[['text'=>'رجوع','callback_data'=>"back"]]
]
])
]);
unlink("data/id/$id/step.txt");
exit;
}

#=========={سيفرات تلجرام جاهز}=========#
if($exdata[0] == "Buynumtele2"){
$api=json_decode(file_get_contents("https://TG-Lion.net?action=country_info&apiKey=$apiKay_Lion&YourID=$Your_ID"),1);
$add=$exdata[1];
$APP = str_replace(["1","2","3"],["السيرفر 1","السيرفر 2","السيرفر 3"],$add);
$a=0;//keyboard
$b=0;//count
foreach($api['countries'] as $zero=>$num){
if($num['add'] == $add){
$price=$num['price'];
$profit_price = $price + ($price * $profit_price_sale / 100);
$country = $num['country'];
$code = $num['code'];
$name = $num['name'];
$b++;
if($b%2!=0){
$key[inline_keyboard][$a][]=[text=>"$name | $$profit_price | $code",callback_data=>"bte-$code"];
}else{
$a++;
$key[inline_keyboard][$a][]=[text=>"$name | $$profit_price | $code",callback_data=>"bte-$code"];
}
}
}
$key['inline_keyboard'][] = [['text'=>'رجوع','callback_data'=>"Buyxvx"]];
$keyboad      = json_encode($key);
if($price == null){
bot('answercallbackquery',[
'callback_query_id'=>$update->callback_query->id,
'text'=>"- عذرا لايوجد دول حاليا في هذا السيفر",
'show_alert'=>false,
]);
unlink("data/id/$id/step.txt");
exit;
}
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
✅ شراء حسابات Telegram جاهزة

- السيرفر: ($APP) 
- رصيدك حسابك: $Balance دولار
",
'parse_mode'=>"MarkDown",
'reply_markup'=>($keyboad),
]);
unlink("data/id/$id/step.txt");
exit;
}
#=========={note}==========#
if($exdata[0] == "bte"){
$codes = $exdata[1];
$api=json_decode(file_get_contents("https://TG-Lion.net?action=country_info&apiKey=$apiKay_Lion&YourID=$Your_ID&country_code=$codes"),1);
$price=$api[price];
$profit_price = $price + ($price * $profit_price_sale / 100);
$add = $api[add];
$name = $api[name];
$APP = str_replace(["1","2","3"],["السيـرفر 1","السيـرفر 2","السيـرفر 3"],$add);
$BALANCE = $Balance - $price;
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
❤ مرحبا عزيزي انت الان علا وشك شراء رقم جاهز لتفعيل Telegram
✅
- سعر الرقم | $$profit_price 
- الدولة | $name
- السيرفر | ($APP) 
",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'شراء حساب','callback_data'=>"getNumber-$codes-$profit_price"]],
[['text'=>' رجوع ','callback_data'=>"Buynumtele2-$add"]]
]
])
]);
unlink("data/id/$id/step.txt");
exit;
}
#=========={Buy Site}==========#
if($exdata[0] == "getNumber"){
$codes = $exdata[1];
$profit_price = $exdata[2];
$api=json_decode(file_get_contents("https://TG-Lion.net?action=country_info&apiKey=$apiKay_Lion&YourID=$Your_ID&country_code=$codes"),1);

if($Balance < $profit_price){
bot('answercallbackquery',[
'callback_query_id' => $update->callback_query->id,
'text'=>"- رصيدك غير كافي $Balance",
'show_alert'=>false,
]);
exit;
}

$api2=json_decode(file_get_contents("https://TG-Lion.net?action=getNumber&apiKey=$apiKay_Lion&YourID=$Your_ID&country_code=$codes"),1);
$price=$api[price];
$profit_price = $price + ($price * $profit_price_sale / 100);
$add = $api[add];
$name = $api[name];
$cod = $api2[cod]; 
$status = $api2[status]; 
$number = $api2[Number]; 
$APP = str_replace(["1","2","3"],["السيرفر 1","السيرفر 2","السيرفر 3"],$add);
$idSend=$ordertelemy;

if($cod == 205){
bot('answercallbackquery',[
'callback_query_id' => $update->callback_query->id,
'text'=>"- عذرا انتهى مخزون هذه الدولة",
'show_alert'=>false,
]);
exit;
}
if($cod == 201){
bot('answercallbackquery',[
'callback_query_id' => $update->callback_query->id,
'text'=>"- يبدو ان اموال قائد الروبوت تحتاج تجديد",
'show_alert'=>false,
]);
exit;
}
if($status == 'error'){
bot('answercallbackquery',[
'callback_query_id' => $update->callback_query->id,
'text'=>"- تم رفض الرقم قم بالشراء مرة اخرى",
'show_alert'=>false,
]);
exit;
}
// اضمن مرور الرقم مع علامة + ي جني
$numbeer = $number;
if(strpos($numbeer, '+') !== 0) {
$numbeer = '+' . $numbeer;
}
$number = str_replace(' ', '', $numbeer);
#__________
if($cod == null and $number != null){
$mr = file_get_contents("ID/$id/points.txt");// مسار رصيد المرسل
$ok = $mr - $profit_price;
$get=bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
✅ تم جلب الرقم بنجاح: 
➖ الدولة : $name 
➖ الرقم : `$number` ☎️
➖ السيـرفر : $APP 🍷
➖ السعر : $profit_price دولار 
➖ الكود : قيد الانتظار 📩
",
'parse_mode'=>"MarkDown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>' جلب كود ','callback_data'=>"getCode-$codes-$number"]]
]
])
]);
bot('sendMessage',[
'chat_id'=>$buy_out,
'text'=>"
✅ تم شراء رقم بنجاح.. 
- الدولة: $name
- الرقم:  $number
- العميل : $id 
- تم خصم : $profit_price دولار
- رصيد العضو: $ok
- السيـرفر : $APP 

",
'parse_mode'=>"MarkDown",
]);
file_put_contents("ID/$id/points.txt",$ok);
unlink("data/id/$id/step.txt");
exit;
}
}
#=========={جزء طلب كود وطلب كود اخر ي ابني}==========#
if($exdata[0] == "getCode"){
$codes=$exdata[1];
$number=$exdata[2];
$api=json_decode(file_get_contents("https://TG-Lion.net?action=country_info&apiKey=$apiKay_Lion&YourID=$Your_ID&country_code=$codes"),1);
$api3=json_decode(file_get_contents("https://TG-Lion.net?action=getCode&number=$number&apiKey=$apiKay_Lion&YourID=$Your_ID"),1);//اذا كنت تريد ان يقوم بجلب الكود وتسجيل الخروج مباشرة مرر هذة القيمة &logout_now=yes عند جلب الكود
$price=$api[price];
$profit_price = $price + ($price * $profit_price_sale / 100);
$add = $api[add];
$name = $api[name];
$code = $api3[code];
$pass = $api3[pass];
$cod = $api3[cod]; 
$message = $api3[message]; 
$status = $api3[status]; 
$APP = str_replace(["1","2","3"],["السيرفر 1","السيرفر 2","السيرفر 3"],$add);
if($codes == null or $number == null){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
- عذرا حدث خطا الدولة او الرقم غير معرف بالنظام
",
'parse_mode'=>"MarkDown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>' رجوع ','callback_data'=>"Buynumtele2-$add"]]
]
])
]);
unlink("data/id/$id/step.txt");
exit;
}
if($code == null and $status == "ok"){
bot('answercallbackquery',[
'callback_query_id' => $update->callback_query->id,
'text'=>"- لم يصل كود لهذا الرقم تأكد من طلب الكود بالطريقة الصحيحة",
'show_alert'=>false,
]);
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
➖ الدولة : $name 
➖ الرقم : `$number` ☎️
➖ السيـرفر : $APP 
➖ السعر : $profit_price دولار 
➖ الكود : قيد الانتظار 📩
",
'parse_mode'=>"MarkDown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>" جلب الكود ",'callback_data'=>"getCode-$codes-$number"]], 
[['text'=>"Logout",'callback_data'=>"logout-$codes-$number"]]
]
])
]);
unlink("data/id/$id/step.txt");
exit;
}
if($status != "ok"){
bot('answercallbackquery',[
'callback_query_id' => $update->callback_query->id,
'text'=>"- وضيفة مرفوضة: $message",
'show_alert'=>false,
]);
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
➖ الدولة : $name 
➖ الرقم : `$number` ☎️
➖ السيـرفر : $APP 
➖ السعر : $profit_price دولار 
➖ الكود : قيد الانتظار 📩
",
'parse_mode'=>"MarkDown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>" جلب الكود ",'callback_data'=>"getCode-$codes-$number"]],
[['text'=>"Logout",'callback_data'=>"logout-$codes-$number"]]
]
])
]);
unlink("data/id/$id/step.txt");
exit;
}
if($status == "ok" and $code != null){
bot('answercallbackquery',[
'callback_query_id' => $update->callback_query->id,
'text'=>"✅ تم وصول الكود بنجاح! رصيدك: $Balance دولار",
'show_alert'=>false,
]);
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
✅ تم وصول رسالة الكود بنجاح ✅
➖ الدولة : $name 
➖ السيـرفر : $APP 
➖ الرقم : `$number` ☎️
➖ الكود : `$code` 💚
➖ السعر : $$profit_price

➖ تم خصم $$profit_price من رصيدك 
➖ المتبقي في رصيدك : $Balance دولار 
",
'parse_mode'=>"MarkDown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"طلب الكود مرة أخرى",'callback_data'=>"getCode-$codes-$number"]],
[['text'=>"Logout",'callback_data'=>"logout-$codes-$number"]]
]
])
]);
bot('SendMessage',[
'chat_id'=>$chat_id,
'text'=>"
⬇️ تم وصول الكود بنجاح بوت $bot_name

✅ 𝐍𝗨𝐌𝐁𝐄𝐑 : `$number`
💬 𝐂𝐎𝐃𝐄 : `$code`
🔐 𝐏𝐀𝐒𝐒 : `$pass`
",
'parse_mode'=>"MarkDown",
'reply_message_id'=>$message_id,
]);
bot('sendMessage',[
'chat_id'=>$PAY,
'text'=>"
- الدولة:  $name 
- ايدي:  `$id` 
- الرقم:  $number 
- الكود:  $code 
- السعر:  $$profit_price
- السيـرفر : $APP 
- المتبقي في رصيدة: $$Balance

- الاستلام: $DAY3 📥
- كود التفعيل: $code 
- التحقق بخطوتين: $pass 
",
'parse_mode'=>"MarkDown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>" تواصل مع العميل ",'url'=>"tg://openmessage?user_id=$id"]]
]
])
]);
$iddd=substr($id, 0,-3)."";
$hnum=substr($number, 0,-4)."";
function sp ($value)
{
    return "<span class=\"tg-spoiler\">$value</span>";
}
bot('SendMessage',[
'chat_id'=>$activation,
'text'=>"
✅ تم شراء رقم بنجاح. 

• الدولة : $name 
• السعر : $$profit_price
• السيـرفر : $APP 
• الرقم  : ×××$hnum 
• العميل : " . sp($iddd) . " 🆔

• المرسل :  Telegram  
• كود التفعيل : $code
",
'disable_web_page_preview'=>true,
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text' => " شراء رقم من البوت ↗️ ", 'url' => "http://t.me/TGLionAPI_bot"]]
]
])
]);
unlink("data/id/$id/step.txt");
}
}
#=========={logout Site}==========#
if($exdata[0] == "logout"){
$codes=$exdata[1];
$number=$exdata[2];
$api=json_decode(file_get_contents("https://TG-Lion.net?action=available_countries&apiKey=$apiKay_Lion&YourID=$Your_ID"),1);
$price=$api[price];
$profit_price = $price + ($price * $profit_price_sale / 100);
$add = $api[add];
$name = $api[name];
$APP = str_replace(["1","2","3"],["السيرفر 1","السيرفر 2","السيرفر 3"],$add);
$api4=json_decode(file_get_contents("https://TG-Lion.net?action=logout_number&number=$number&apiKey=$apiKay_Lion&YourID=$Your_ID"),1);
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
- الرقم: `$number`
- الدولة: $name
- البرنامج: تيليجرام

✅ تم تسجيل الخروج من الرقم بنجاح!
",
'parse_mode'=>"MarkDown",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'شراء مرة اخرى','callback_data'=>"getNumber-$codes"]],
[['text'=>' رجوع ','callback_data'=>"Buynumtele2-$add"]]
]
])
]);
bot('sendMessage',[
'chat_id'=>$buy_out,
'text'=>"
🔓 تم تسجيل خروج رقم جاهز

• الدولة: $name 
• الرقم:  $number 
• سعر الرقم:  $$profit_price 
• ايدي العضو: $id
• رصيد العضو: $Balance 
• السيرفر : $APP 
",
'parse_mode'=>"MarkDown",
]);
unlink("data/id/$id/step.txt");
exit;
}
///////////// النهايه لقصه الحب هذه 






#====================================================#
#====================================================#
#====================================================#
#==================== واجه تحكم الادارة ==================#
if($id == $Your_ID){
if($text == '/admin'){
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"
💻: هذه لوحة التحكم الخاصة بإدارة الروبوت
",
'reply_to_message_id'=>$message_id,
'reply_markup'=>json_encode([ 
'inline_keyboard'=>[
[['text'=>"شحن رصيد",'callback_data'=>"TTT"],['text'=>"خصم رصيد",'callback_data'=>"LLL"]],
[['text'=>"عرض الدول المتوفرة",'callback_data'=>"Available_tele"]], 
[['text'=>"✅ نظام تأكيد ورفظ الارقام ✅",'callback_data'=>"Pending_Numbers"]]

]
])
]);
unlink("data/id/$id/step.txt");
}
}
// لرجوع
if($data == 'AA'){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
💻: هذه لوحة التحكم الخاصة بإدارة الروبوت
",
'reply_to_message_id'=>$message_id,
'reply_markup'=>json_encode([ 
'inline_keyboard'=>[
[['text'=>"شحن رصيد",'callback_data'=>"TTT"],['text'=>"خصم رصيد",'callback_data'=>"LLL"]],
[['text'=>"عرض الدول المتوفرة",'callback_data'=>"Available_tele"]]
]
])
]);
unlink("data/id/$id/step.txt");
}
//////
include("admin.php");
//////
?>