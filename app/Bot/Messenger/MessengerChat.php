<?php

namespace App\Bot\Messenger;


use App\Bot\Dialogs;
use App\Bot\Interfaces\ChatInterface;
use App\Bot\Interfaces\ClientInterface;
use App\PSN\Interfaces\DTO\ProductInterface;
use Kerox\Messenger\Model\Common\Button\Postback;
use Kerox\Messenger\Model\Message;
use Kerox\Messenger\Model\Common\Button\WebUrl;
use Kerox\Messenger\Model\Message\Attachment\Template\Element\GenericElement;

class MessengerChat implements ChatInterface
{

    public const POSTBACK_ACTION_REMOVE_ITEM = 'action_remove_item';

    /**
     * @var \App\Bot\Messenger\MessengerClient
     */
    protected $client;

    /**
     * @var \App\Bot\Dialogs
     */
    protected $dialogs;

    /**
     * MessengerChat constructor.
     * @param \App\Bot\Messenger\MessengerClient $messengerClient
     * @param \App\Bot\Dialogs $dialogs
     */
    public function __construct(MessengerClient $messengerClient, Dialogs $dialogs)
    {
        $this->client = $messengerClient;
        $this->dialogs = $dialogs;
    }

    /**
     * @return \App\Bot\Messenger\MessengerClient
     */
    public function client(): ClientInterface
    {
        return $this->client;
    }

    /**
     * @param $userId
     */
    public function greet($userId)
    {
        if ($messages = $this->dialogs->getGreeting()) {
            $this->sendMessages($userId, $messages);
        }
    }

    /**
     * @param $userId
     */
    public function sorry($userId) {
        if ($messages = $this->dialogs->getSorry()) {
            $this->sendMessages($userId, $messages);
        }
    }

    /**
     * @param $userId
     */
    public function wrongInput($userId)
    {
        if ($messages = $this->dialogs->getWrongInput()) {
            $this->sendMessages($userId, $messages);
        }
    }

    public function itemAdded($userId)
    {
        if ($messages = $this->dialogs->getItemAdded()) {
            $this->sendMessages($userId, $messages);
        }
    }

    public function itemRemoved($userId)
    {
        if ($messages = $this->dialogs->getItemRemoved()) {
            $this->sendMessages($userId, $messages);
        }
    }

    public function emptyWatchList($userId)
    {
        if ($messages = $this->dialogs->getEmptyWatchList()) {
            $this->sendMessages($userId, $messages);
        }
    }

    public function sendProductCards($userId, array $cards)
    {
        $message = Message\Attachment\Template\GenericTemplate::create($cards);
        $this->client->sendMessage($userId, $message);
    }

    public function getRemoveButton(string $id)
    {
        $payload = sprintf(
            '%s:%s',
            self::POSTBACK_ACTION_REMOVE_ITEM,
            $id
        );

        return Postback::create(
            __('ui.btn_remove_from_watchlist'),
            $payload
        );
    }

    /**
     * @param ProductInterface $product
     * @param bool $isWatchlist
     * @return GenericElement
     * @throws \Kerox\Messenger\Exception\MessengerException
     */
    public function getSingleProductCard(ProductInterface $product, bool $isWatchlist = false)
    {
        $buttons = [
            WebUrl::create(
                __('ui.btn_open_in_store'),
                $product->getStoreUrl()
            )
        ];

        if ($isWatchlist) {
            $buttons = array_merge(
                $buttons,
                [
                    $this->getRemoveButton(
                        $product->getId()
                    )
                ]
            );
        }

        return GenericElement::create($product->getTitleAsText())
            ->setImageUrl($product->getImageUrl())
            ->setSubtitle($product->getPricesAsText())
            ->setButtons($buttons);
    }

    /**
     * @param $userId
     * @param array $messages
     * @param int $delay
     */
    protected function sendMessages($userId, array $messages, $delay = 3500)
    {
        foreach ($messages as $message) {
            try {
                $this->client->sendMessage($userId, $message);
//                $this->client->typeOn($userId);
                usleep($delay);
//                $this->client->typeOff($userId);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}