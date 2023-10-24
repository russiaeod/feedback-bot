<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;
use Russiaeod\FeedbackBot\App;

Dotenv::createImmutable (__DIR__,'.env')->load();

$log = (new Logger($_ENV['NAME_BOT']))->pushHandler(new StreamHandler(__DIR__.'/logs/'.$_ENV['NAME_BOT'].'.log', Logger::INFO));

try {
    $bot = new BotApi($_ENV['ACCESS_TOKEN']);

    $client = new Client($_ENV['ACCESS_TOKEN']);
    $client->on(function (Update $update) use ($bot) {
        $message = $update->getMessage();
        $user_id = $message->getChat()->getId();
        $message_id = $message->getMessageId();
        $chat_id = $message->getChat()->getId();
        $array_users_ids = file(__DIR__.'/app/database/users.txt');
        if (in_array($user_id,$array_users_ids)) {
            $answerMessage = '&#128276; <b>Новое обращение от пользователя:</b> '.PHP_EOL.
                'ID: '.$user_id.PHP_EOL.
                'First name: '.$message->getFrom()->getFirstName().PHP_EOL.
                'Last name: '.$message->getFrom()->getLastName().PHP_EOL.
                'Username: '.$message->getFrom()->getUsername().PHP_EOL.
                'Language_code: '.$message->getFrom()->getLanguageCode();
            $bot->sendMessage($_ENV['ADMIN_ID'], $answerMessage,'html');
            $bot->forwardMessage($_ENV['ADMIN_ID'],$chat_id, $message_id);
            $bot->sendMessage($user_id, file_get_contents(__DIR__ . '/app/config/successMessage.txt'),'html');
        } else {
            //нет пользователя
            $string_user_id = (string) $user_id.PHP_EOL;
            file_put_contents(__DIR__.'/app/database/users.txt', $string_user_id, FILE_APPEND);
            $bot->sendMessage($user_id, file_get_contents(__DIR__ . '/app/config/startMessage.txt'),'html');
        }

    }, function () {
        return true;
    });

    $client->run();

} catch (\TelegramBot\Api\Exception|Exception $e) {
    $log->emergency($e->getMessage());
}

