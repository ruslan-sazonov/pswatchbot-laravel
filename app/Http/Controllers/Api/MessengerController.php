<?php

namespace App\Http\Controllers\Api;


use App\Bot\Manager;
use App\Bot\DTO\Product;
use App\PSN\Store as PsnStore;
use App\Models\MessengerWatchItem;
use App\Models\MessengerUser;
use App\Http\Controllers\Controller;

class MessengerController extends Controller
{

    /**
     * @var \App\Bot\Messenger\MessengerChat
     */
    protected $bot;

    /**
     * @var \App\PSN\Store
     */
    protected $store;

    /**
     * @var \App\Bot\Messenger\MessengerClient
     */
    protected $client;

    /**
     * MessengerController constructor.
     * @param \App\Bot\Manager $botManager
     * @param \App\PSN\Store $store
     */
    public function __construct(Manager $botManager, PsnStore $store)
    {
        ini_set("xdebug.overload_var_dump", "off");
        $this->bot = $botManager->messenger();
        $this->store = $store;
        $this->client = $this->bot->client();
    }

    public function challenge()
    {
        if (!$this->client->isValidToken()) {
            abort(400, 'Invalid Token');
        }

        echo $this->client->getChallenge();
        response('OK', 200);
    }

    public function webhook()
    {
        try {
            if ($events = $this->client->getEvents()) {
                foreach ($events as $event) {
                    $this->handleEvent($event);
                }
            }
        } catch (\Exception $e) {
            dump($e->getMessage());
        }

        response('OK', 200);
    }

    /**
     * @param \Kerox\Messenger\Event\AbstractEvent $event
     */
    protected function handleEvent($event)
    {
        $senderId = $event->getSenderId();
        //TODO: try to find user and configure locale

        if ($this->client->isMessageEvent($event)) {
            try {
                $this->handleMessage($event);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        if ($this->client->isPostbackEvent($event)) {
            try {
                $this->handlePostBack($event);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    /**
     * Handles a postback message
     *
     * @param \Kerox\Messenger\Event\PostbackEvent $event
     * @throws \Kerox\Messenger\Exception\MessengerException
     */
    protected function handlePostBack($event)
    {
        $senderId = $event->getSenderId();
        $payload = $event->getPostback()->getPayload();

        switch($payload) {
            case 'action_start':
                $this->createOrUpdateUser(
                    $senderId,
                    $this->getUser($senderId)
                );

                $this->bot->greet($senderId);
                break;
            case 'action_show_watch_list':
                //TODO: implement
                break;
            case 'action_show_help':
                //TODO: implement
                break;
        }
    }

    /**
     * @param $event
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function handleMessage($event)
    {
        $message = $event->getMessage();
        $senderId = $event->getSenderId();

        if ($message->hasText()) {
            $input = $message->getText();

            if ($this->store->isValidStoreFrontURL($input)) {
                if ($productData = $this->store->getGameDataByURL($input)) {
                    if ($productId = $this->createOrUpdateWatchItem($productData, $senderId)) {
                        $this->bot->itemAdded($senderId);

                        $product = new Product($productData);
                        $productCard = $this->bot->getSingleProductCard($product);
                        $this->bot->sendProductCard($senderId, $productCard);
                        die();

                    } else {
                        $this->bot->sorry($senderId);
                    }
                } else {
                    $this->bot->wrongInput($senderId);
                }
            } else {
                $this->bot->wrongInput($senderId);
            }
        }

//        if ($message->hasQuickReply()) {
//            echo $message->getQuickReply();
//        }
//
//        if ($message->hasAttachments()) {
//            print_r($message->getAttachments());
//        }
    }

    /**
     * Get a user info
     *
     * @param int $uid
     * @return \Kerox\Messenger\Response\UserResponse
     * @throws \Kerox\Messenger\Exception\MessengerException
     */
    protected function getUser(int $uid)
    {
        return $this->client->getUserProfile($uid);
    }

    /**
     * @param int $recipientId
     * @param \Kerox\Messenger\Response\UserResponse $user
     */
    protected function createOrUpdateUser(int $recipientId, $user)
    {
        $userModel = MessengerUser::firstOrNew(
            ['recipient_id' => $recipientId]
        );

        $userModel->recipient_id = $recipientId;
        $userModel->first_name = $user->getFirstName();
        $userModel->last_name = $user->getLastName();
        $userModel->locale = $user->getLocale();
        $userModel->timezone = $user->getTimezone();
        $userModel->save();
    }

    /**
     * @param array $item
     * @param int $userId
     * @return int
     */
    protected function createOrUpdateWatchItem(array $item, int $userId): int
    {
        $itemModel = MessengerWatchItem::firstOrNew([
            'recipient_id' => $userId,
            'product_id' => $item['api-handle']
        ]);

        $itemModel->recipient_id = $userId;
        $itemModel->product_id = $item['api-handle'] ?? null;
        $itemModel->name = $item['name'] ?? null;
        $itemModel->image = $item['image'] ?? null;
        $itemModel->store_url = $item['store-url'] ?? null;
        $itemModel->api_url = $item['api-url'] ?? null;
        $itemModel->prices = $item['prices'] ?? null;
        $itemModel->fetched_at = $item['fetched-at'] ?? null;
        $itemModel->save();

        return $itemModel->id;
    }
}
