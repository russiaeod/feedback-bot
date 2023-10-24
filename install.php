<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use TelegramBot\Api\BotApi;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

Dotenv::createImmutable(__DIR__, '.env')->load();

$log = (new Logger($_ENV['NAME_BOT']))->pushHandler(new StreamHandler(__DIR__ . '/logs/' . $_ENV['NAME_BOT'] . '.log', Logger::INFO));

try {
    $bot = new BotApi($_ENV['ACCESS_TOKEN']);
    $result = $bot->setWebhook($_ENV['URL_BOT']);
    if ($result) {
        $log->info('Webhook installed');
    }

} catch (Exception) {
    $log->emergency('webhook is not installed');
}
