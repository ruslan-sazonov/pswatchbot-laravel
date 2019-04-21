<?php

namespace App\Bot\Messenger;


use App\Bot\Interfaces\ClientInterface;
use Kerox\Messenger\Api\Send;
use Kerox\Messenger\Messenger;
use Kerox\Messenger\Event\AbstractEvent;
use Kerox\Messenger\Event\MessageEvent;
use Kerox\Messenger\Event\PostbackEvent;
use Kerox\Messenger\Response\UserResponse;

class MessengerClient implements ClientInterface
{

    /**
     * @var \Kerox\Messenger\Messenger
     */
    protected $sdk;

    /**
     * MessengerClient constructor.
     * @param \Kerox\Messenger\Messenger $sdk
     */
    public function __construct(Messenger $sdk)
    {
        $this->sdk = $sdk;
    }

    /**
     * Gets current events
     *
     * @return array
     * @throws \Exception
     */
    public function getEvents(): array
    {
        return $this->sdk->webhook()->getCallbackEvents();
    }

    /**
     * Gets user's profile
     *
     * @param int $userId
     * @return \Kerox\Messenger\Response\UserResponse
     * @throws \Kerox\Messenger\Exception\MessengerException
     */
    public function getUserProfile(int $userId): UserResponse
    {
        return $this->sdk->user()->profile($userId);
    }

    /**
     * Gets a webhook challenge
     *
     * @return string
     */
    public function getChallenge()
    {
        return $this->sdk->webhook()->challenge();
    }

    /**
     * Checks if the incoming token is valid
     *
     * @return bool
     */
    public function isValidToken(): bool
    {
        return $this->sdk->webhook()->isValidToken();
    }

    /**
     * Checks if the incoming request is valid
     *
     * @return bool
     * @throws \Exception
     */
    public function isValidCallback(): bool
    {
        return $this->sdk->webhook()->isValidCallback();
    }

    /**
     * Checks if event is a Message event
     *
     * @param $event
     * @return bool
     */
    public function isMessageEvent($event): bool
    {
        return $event instanceof MessageEvent;
    }

    /**
     * Checks if event is a Postback event
     *
     * @param $event
     * @return bool
     */
    public function isPostbackEvent($event): bool
    {
        return $event instanceof PostbackEvent;
    }

    /**
     * @param $userId
     * @param $message
     * @throws \Exception
     */
    public function sendMessage($userId, $message)
    {
        $this->sdk->send()->message($userId, $message);
    }

    /**
     * @param $userId
     * @throws \Exception
     */
    public function typeOn($userId)
    {
        $this->sdk->send()->message($userId, Send::SENDER_ACTION_TYPING_ON);
    }

    /**
     * @param $userId
     * @throws \Exception
     */
    public function typeOff($userId)
    {
        $this->sdk->send()->message($userId, Send::SENDER_ACTION_TYPING_OFF);
    }
}