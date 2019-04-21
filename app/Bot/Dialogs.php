<?php

namespace App\Bot;


use Spatie\Emoji\Emoji;

class Dialogs
{

    public function getGreeting(): array
    {
        return [
            Emoji::wavingHand(),
            __('bot.greeting_msg_1'),
            __('bot.greeting_msg_2'),
            __('bot.greeting_msg_3'),
            __('bot.greeting_msg_4'),
        ];
    }

    public function getWrongInput(): array
    {
        return [
            //TODO: emoji
            __('bot.wrong_input_msg_1'),
            __('bot.wrong_input_msg_2'),
        ];
    }

    public function getItemAdded(): array
    {
        return [
            Emoji::thumbsUp(),
            __('bot.added_msg')
        ];
    }

    public function getSorry(): array
    {
        return [
            //TODO: emoji
            'bot.sorry_msg'
        ];
    }
}