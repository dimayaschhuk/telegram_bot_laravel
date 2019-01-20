<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function index()
    {

// создаем переменную бота
$token = "607965346:AAHpeh8rhizXsQBQOzPv6h1EPKuUUA6EI2I";
$bot = new \TelegramBot\Api\Client($token,null);


// если бот еще не зарегистрирован - регистируем
if(!file_exists("registered.trigger")){
    /**
     * файл registered.trigger будет создаваться после регистрации бота.
     * если этого файла нет значит бот не зарегистрирован
     */

    // URl текущей страницы
    $page_url = "https://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    $result = $bot->setWebhook($page_url);
    if($result){
        file_put_contents("registered.trigger",time()); // создаем файл дабы прекратить повторные регистрации
    } else die("ошибка регистрации");
}

// Команды бота
// пинг. Тестовая
$bot->command('ping', function ($message) use ($bot) {
    $bot->sendMessage($message->getChat()->getId(), 'pong!');
});

// обязательное. Запуск бота
$bot->command('start', function ($message) use ($bot) {
    $answer = 'Добро пожаловать! Чем могу бить полезним';
    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
        [
            [
                ['callback_data' => 'main1', 'text' => 'росписание'],
                ['callback_data' => 'main2', 'text'=>"Отправить номер телефона", 'request_contact'=>True]
            ]
        ]
    );
    $bot->sendMessage($message->getChat()->getId(), $answer, false, null,null,$keyboard);
});

// помощ
$bot->command('help', function ($message) use ($bot) {
    $answer = 'Команды:
/help - помощ';
    $bot->sendMessage($message->getChat()->getId(), $answer);
});

// передаем картинку
$bot->command('getpic', function ($message) use ($bot) {
    $pic = "http://aftamat4ik.ru/wp-content/uploads/2017/03/photo_2016-12-13_23-21-07.jpg";

    $bot->sendPhoto($message->getChat()->getId(), $pic);
});

// передаем документ
$bot->command('getdoc', function ($message) use ($bot) {
    $document = new \CURLFile('shtirner.txt');

    $bot->sendDocument($message->getChat()->getId(), $document);
});

// Кнопки у сообщений
$bot->command("ibutton", function ($message) use ($bot) {
    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
        [
            [
                ['callback_data' => 'data_test', 'text' => 'Answer'],
                ['callback_data' => 'data_test2', 'text' => 'ОтветЪ']
            ]
        ]
    );

    $bot->sendMessage($message->getChat()->getId(), "тест", false, null,null,$keyboard);
});

// Обработка кнопок у сообщений
$bot->on(function($update) use ($bot, $callback_loc, $find_command){
    $callback = $update->getCallbackQuery();
    $message = $callback->getMessage();
    $chatId = $message->getChat()->getId();
    $data = $callback->getData();


    if($data == "main1"){
        $bot->answerCallbackQuery( $callback->getId(), "На сегодня пар нет",true);
    }
    if($data == "main2"){

        $bot->sendMessage($message->getChat()->getId(), $message['phone_number']);
    }


    if($data == "data_test"){
        $bot->answerCallbackQuery( $callback->getId(), "This is Ansver!",true);
    }
    if($data == "data_test2"){
        $bot->sendMessage($chatId, "На сегодня пар нет");
        $bot->answerCallbackQuery($callback->getId()); // можно отослать пустое, чтобы просто убрать "часики" на кнопке
    }

}, function($update){
    $callback = $update->getCallbackQuery();
    if (is_null($callback) || !strlen($callback->getData()))
        return false;
    return true;
});

// обработка инлайнов
$bot->inlineQuery(function ($inlineQuery) use ($bot) {
    mb_internal_encoding("UTF-8");
    $qid = $inlineQuery->getId();
    $text = $inlineQuery->getQuery();

    // Это - базовое содержимое сообщения, оно выводится, когда тыкаем на выбранный нами инлайн
    $str = "Что другие?
Свора голодных нищих.
Им все равно...
В этом мире немытом
Душу человеческую
Ухорашивают рублем,
И если преступно здесь быть бандитом,
То не более преступно,
Чем быть королем...
Я слышал, как этот прохвост
Говорил тебе о Гамлете.
Что он в нем смыслит?
<b>Гамлет</b> восстал против лжи,
В которой варился королевский двор.
Но если б теперь он жил,
То был бы бандит и вор.";
    $base = new \TelegramBot\Api\Types\Inline\InputMessageContent\Text($str,"Html");

    // Это список инлайнов
    // инлайн для стихотворения
    $msg = new \TelegramBot\Api\Types\Inline\QueryResult\Article("1","С. Есенин","Отрывок из поэмы `Страна негодяев`");
    $msg->setInputMessageContent($base); // указываем, что в ответ к этому сообщению надо показать стихотворение

    // инлайн для картинки
    $full = "http://aftamat4ik.ru/wp-content/uploads/2017/05/14277366494961.jpg"; // собственно урл на картинку
    $thumb = "http://aftamat4ik.ru/wp-content/uploads/2017/05/14277366494961-150x150.jpg"; // и миниятюра

    $photo = new \TelegramBot\Api\Types\Inline\QueryResult\Photo("2",$full,$thumb);

    // инлайн для музыки
    $url = "http://aftamat4ik.ru/wp-content/uploads/2017/05/mongol-shuudan_-_kozyr-nash-mandat.mp3";
    $mp3 = new \TelegramBot\Api\Types\Inline\QueryResult\Audio("3",$url,"Монгол Шуудан - Козырь наш Мандат!");

    // инлайн для видео
    $vurl = "http://aftamat4ik.ru/wp-content/uploads/2017/05/bb.mp4";
    $thumb = "http://aftamat4ik.ru/wp-content/uploads/2017/05/joker_5-150x150.jpg";
    $video = new \TelegramBot\Api\Types\Inline\QueryResult\Video("4",$vurl,$thumb, "video/mp4","коммунальные службы","тут тоже может быть описание");

    // отправка
    try{
        $result = $bot->answerInlineQuery( $qid, [$msg,$photo,$mp3,$video],100,false);
    }catch(Exception $e){
        file_put_contents("rdata",print_r($e,true));
    }
});

// Reply-Кнопки
$bot->command("buttons", function ($message) use ($bot) {
    $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([[["text" => "Власть советам!"], ["text" => "Сиськи!"]]], true, true);

    $bot->sendMessage($message->getChat()->getId(), "тест", false, null,null, $keyboard);
});

// Отлов любых сообщений + обрабтка reply-кнопок
$bot->on(function($Update) use ($bot){

    $message = $Update->getMessage();
    $mtext = $message->getText();
    $cid = $message->getChat()->getId();

    $amswer[]=$message.'/'.$mtext.'/'.$cid;
    print_r($amswer);
    $bot->sendMessage($message->getChat()->getId(), 'qq');

}, function($message) use ($name){
    return true; // когда тут true - команда проходит
});

// запускаем обработку
$bot->run();

echo "бот";
    }

    public function setWebhook()
    {}
}
