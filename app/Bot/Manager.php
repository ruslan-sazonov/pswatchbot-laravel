<?php

namespace App\Bot;


use App\Bot\Messenger\MessengerChat;

class Manager
{

    /**
     * @var \App\Bot\Messenger\MessengerChat
     */
    protected $messengerChat;

    //TODO: implement along with telegram
    protected $telegramClient = null;

    /**
     * Manager constructor.
     * @param \App\Bot\Messenger\MessengerChat $messengerChat
     */
    public function __construct(MessengerChat $messengerChat)
    {
        $this->messengerChat = $messengerChat;
    }

    /**
     * @return \App\Bot\Messenger\MessengerChat
     */
    public function messenger(): MessengerChat
    {
        return $this->messengerChat;
    }
}