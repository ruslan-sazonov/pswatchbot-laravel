<?php
namespace App\Bot\Interfaces;


interface ClientInterface
{
    public function sendMessage($userId, $message);
}