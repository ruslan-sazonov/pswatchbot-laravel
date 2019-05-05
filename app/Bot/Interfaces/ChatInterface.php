<?php

namespace App\Bot\Interfaces;


interface ChatInterface
{
    public function client(): ClientInterface;
    public function greet($userId);
    public function wrongInput($userId);
    public function itemAdded($userId);
    public function itemRemoved($userId);
    public function sorry($userId);
    public function emptyWatchList($userId);
}