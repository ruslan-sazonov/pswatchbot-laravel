<?php

namespace App\Bot\Messenger;


use App\Bot\Dialogs;
use App\Bot\Interfaces\ChatInterface;
use App\Bot\Interfaces\ClientInterface;
use App\Bot\Interfaces\DTO\ProductInterface;
use Kerox\Messenger\Model\Message;
use Kerox\Messenger\Model\Common\Button\WebUrl;
use Kerox\Messenger\Model\Message\Attachment\Template\Element\GenericElement;

class MessengerChat implements ChatInterface
{

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

    public function sendProductCard($userId, $card)
    {
        $message = Message\Attachment\Template\GenericTemplate::create([$card]);
        $this->client->sendMessage($userId, $message);
    }

    /**
     * @param \App\Bot\Interfaces\DTO\ProductInterface $product
     * @return GenericElement
     * @throws \Kerox\Messenger\Exception\MessengerException
     */
    public function getSingleProductCard(ProductInterface $product)
    {
        //TODO: get a proper set of buttons

        return GenericElement::create($product->getTitleAsText())
            ->setImageUrl($product->getImageUrl())
            ->setSubtitle($product->getPricesAsText())
            ->setButtons([
                WebUrl::create('Open in Store', $product->getStoreUrl())
            ]);
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