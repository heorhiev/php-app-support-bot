<?php

namespace app\supportBot\commands;

use app\bot\models\Message;
use app\supportBot\utils\Forum;
use app\supportBot\entities\Contact;
use app\supportBot\constants\SupportBotConst;
use app\toolkit\services\LoggerService;


class NotifyCommand extends \app\bot\models\Command
{
    public function run(): void
    {
        if ($this->getBot()->getIncomeMessage()->getChat()->getId() != $this->getBot()->getOptions()->data['support']['forum']) {
            return;
        }

        $contacts = Contact::repository()->select(['id'])->filter([
            'status' => SupportBotConst::CONTACT_STATUS_ACTIVE,
        ])->asArrayAll();

        $notifyText = trim($this->getBot()->getIncomeMessage()->getParams());

        $message = $this->getBot()->getNewMessage()->setMessageView('notify')->setAttributes([
            'countRecipients' => count($contacts),
            'notifyText' => $notifyText,
        ]);

        $this->getBot()->sendMessage($message);

        $message = $this->getBot()->getNewMessage();
        $message->setMessageText($notifyText);


        foreach ($contacts as $contact) {
            $message->setRecipientId($contact['id']);

            try {
                $this->getBot()->sendMessage($message);
                $this->sendFiles($contact['id']);
            } catch (\Exception $exception) {
                LoggerService::error($exception);
            }
        }
    }


    private function sendFiles($chatId)
    {
        $message = $this->getBot()->getDataFromRequest()->getMessage();

        if ($message->getVideo()) {
            $fileId = $message->getVideo()->getFileId();
            $this->getBot()->getBotApi()->sendVideo($chatId, $fileId);
        }

        if ($photos = $message->getPhoto()) {
            $fileId = end($photos)->getFileId();
            $this->getBot()->getBotApi()->sendPhoto($chatId, $fileId);
        }

        if ($message->getDocument()) {
            $fileId = $message->getDocument()->getFileId();
            $this->getBot()->getBotApi()->sendDocument($chatId, $fileId);
        }

        if ($message->getVoice()) {
            $fileId = $message->getVoice()->getFileId();
            $this->getBot()->getVoice()->sendVoice($chatId, $fileId);
        }
    }
}