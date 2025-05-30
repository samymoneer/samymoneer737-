<?php

#=========={الارقام المتوفرة لتيليجرام}==========#
if ($data=="Available_tele"){
$api_url="https://TG-Lion.net?action=available_countries&apiKey=$apiKay_Lion&YourID=$Your_ID";
$response=json_decode(file_get_contents($api_url),true);
if ($response["status"]&&isset($response["countries"])){
$countries=$response["countries"];
$message="✅ الدول المتوفر لها مخزون حاليا هي:\n\n";
$counter=1;
foreach ($countries as $country){
$message.="$counter/ ".$country["name"]." | ".$country["code"]." •\n";
$counter++;
}
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>$message,
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'رجوع','callback_data'=>"AA"]]
]
])
]);
}else{
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"لم يتم العثور على دول متاحة.",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>'رجوع','callback_data'=>"AA"]]
]
])
]);
}
}

#=========={شحن رصيد}==========#
if($data == 'TTT'){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
قم بإرسال ID العميل الذي تريد تحويل الاموال الية
",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"AA"]]
]
])
]);
file_put_contents("data/id/$id/step.txt","TTT");
}
if($text && $text != '/start' && $step == 'TTT'){
$ttt = json_decode(file_get_contents("ID/$text/points.txt"),true);
if (!isset($ttt)) {
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"
- عذرا هاذا المستخدم غير موجود في الروبوت
",
'reply_to_message_id'=>$message_id,
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"AA"]]
]
])
]);
}else{
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"
- إرسل عدد الدولارات اللتي تريد تحويلها لهذا المستخدم ($text) 
",
'reply_to_message_id'=>$message_id,
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"AA"]]
]
])
]);
file_put_contents("data/id/$id/step.txt","TTT2|$text");
}
}
if($text && $text != '/start' && $exstep[0] == 'TTT2'){
$idEM = $exstep[1];
$ms = file_get_contents("ID/$idEM/points.txt");// مسار رصيد المستلم
$ok = $ms + $text;
file_put_contents("ID/$idEM/points.txt",$ok);
bot('sendMessage',[
'chat_id'=>$idEM,
'text'=>"
- تم شحن  $price دولار الى حسابك ✅.
- تم الشحن بواسطة ادارة البوت
- رصيدك الحالي  $ok دولار
",
'parse_mode'=>"MarkDown",
]);
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"
- تم شحن  $price دولار الى `$idEM` ✅.
- تم الشحن بواسطة ادارة البوت
- رصيد المستخدم الحالي  $ok دولار
",
'parse_mode'=>"MarkDown",
'reply_to_message_id'=>$message_id,
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"تواصل مع العميل",'url'=>"tg://openmessage?user_id=$idEM"]],
]
])
]);
unlink("data/id/$id/step.txt");
}
#=========={خصم رصيد}==========#
if($data == 'LLL'){
bot('EditMessageText',[
'chat_id'=>$chat_id,
'message_id'=>$message_id,
'text'=>"
قم بإرسال ID العميل الذي تريد خصم الاموال منة
",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"AA"]]
]
])
]);
file_put_contents("data/id/$id/step.txt","LLL");
}
if($text && $text != '/start' && $step == 'LLL'){
$ttt = json_decode(file_get_contents("ID/$text/points.txt"),true);
if (!isset($ttt)) {
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"
- عذرا هاذا المستخدم غير موجود في الروبوت
",
'reply_to_message_id'=>$message_id,
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"AA"]]
]
])
]);
}else{
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"
- إرسل عدد الدولارات اللتي تريد خصمها من المستخدم ($text) 
",
'reply_to_message_id'=>$message_id,
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"رجوع",'callback_data'=>"AA"]]
]
])
]);
file_put_contents("data/id/$id/step.txt","LLL2|$text");
}
}
if($text && $text != '/start' && $exstep[0] == 'LLL2'){
$idEM = $exstep[1];
$ms = file_get_contents("ID/$idEM/points.txt");// مسار رصيد المستلم
$ok = $ms - $text;
file_put_contents("ID/$idEM/points.txt",$ok);
bot('sendMessage',[
'chat_id'=>$idEM,
'text'=>"
- تم خصم  $text دولار من حسابك ✅.
- تم الخصم بواسطة ادارة البوت
- رصيدك الحالي  $ok دولار
",
'parse_mode'=>"MarkDown",
]);
bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>"
- تم خصم  $price دولار من المستخدم `$idEM` ✅.
- تم الخصم بواسطة ادارة البوت
- رصيد المستخدم الحالي  $ok دولار
",
'parse_mode'=>"MarkDown",
'reply_to_message_id'=>$message_id,
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"تواصل مع العميل",'url'=>"tg://openmessage?user_id=$idEM"]],
]
])
]);
unlink("data/id/$id/step.txt");
exit;
}
?> 