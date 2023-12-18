<?php

namespace app\supportBot\commands;

use app\bot\models\Message;
use app\supportBot\constants\SupportBotConst;
use app\supportBot\entities\Contact;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;


class StartCommand extends \app\bot\models\Command
{
    public function run(): void
    {
        self::createContact($this->getBot()->getUserId());

        $message = $this->getBot()->getNewMessage();

        $menu = $this->getBot()->getMenu();

        $buttons  = [];
        foreach ($menu as $command => $text) {
            $buttons[] = [['text' => $text, 'callback_data' => $command]];
        }

        $keyboard = new ReplyKeyboardMarkup($buttons, false, true, true);

        $message->setKeyboardMarkup($keyboard);

//        foreach ($menu as $command => $text) {
//            $buttons[] = [[
//                'text' => $text,
//                'callback_data' => $command
//            ]];
//        }
//
//        $message->setKeyboardMarkup($buttons);

        $message->setMessageView('start');

        $this->getBot()->sendMessage($message);
    }


    private static function createContact($userId): void
    {
        Contact::repository()->delete(['id' => $userId]);

        Contact::repository()->create([
            'id' => $userId,
            'status' => SupportBotConst::CONTACT_STATUS_ACTIVE,
        ]);
    }
}