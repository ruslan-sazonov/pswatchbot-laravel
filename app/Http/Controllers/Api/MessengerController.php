<?php

namespace App\Http\Controllers\Api;


use App\Bot\Manager;
use App\Bot\Messenger\MessengerChat;
use App\PSN\DTO\Product;
use App\PSN\Store as PsnStore;
use App\Models\MessengerWatchItem;
use App\Models\MessengerUser;
use App\Http\Controllers\Controller;
use Kerox\Messenger\Model\Message\Attachment\Template\Element\GenericElement;
use Illuminate\Support\Collection;

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
     * @var int
     */
    protected $userId;

    /**
     * MessengerController constructor.
     * @param \App\Bot\Manager $botManager
     * @param \App\PSN\Store $store
     */
    public function __construct(Manager $botManager, PsnStore $store)
    {
        ini_set("xdebug.overload_var_dump", "off");
        $this->bot = $botManager->messenger();
        $this->client = $this->bot->client();
        $this->store = $store;
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

    protected function configureLocale(): void
    {
        //TODO: try to find user and configure locale
    }

    /**
     * @param $event
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function handleEvent($event)
    {
        $this->userId = $event->getSenderId();
        $this->configureLocale();

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
        $payload = $event->getPostback()->getPayload();

        switch($payload) {
            case 'action_start':
                $this->createOrUpdateUser(
                    $this->getUserProfile($this->userId)
                );

                $this->bot->greet($this->userId);
                break;
            case 'action_show_watch_list':
                $this->sendWatchList();
                break;
            case 'action_show_help':
                //TODO: implement
                break;
            default:
                $this->handlePostBackButton($payload);
                break;
        }
    }

    /**
     * @param $payload
     */
    protected function handlePostBackButton($payload): void
    {
        $payload = explode(':', $payload);

        if (count($payload) == 2) {
            $action = $payload[0];
            $identifier = $payload[1];

            switch ($action) {
                case MessengerChat::POSTBACK_ACTION_REMOVE_ITEM:
                    if ($this->removeItemFromWatchlist($identifier)) {
                        $this->bot->itemRemoved($this->userId);
                    } else {
                        $this->bot->sorry($this->userId);
                    }
                    break;
            }
        }
    }

    /**
     * @param $event
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Kerox\Messenger\Exception\MessengerException
     */
    protected function handleMessage($event)
    {
        $message = $event->getMessage();

        if ($message->hasText()) {
            $input = $message->getText();

            if ($this->store->isValidStoreFrontURL($input)) {
                if ($productData = $this->store->getGameDataByURL($input)) {
                    if ($productId = $this->createOrUpdateWatchItem($productData)) {
                        $this->bot->itemAdded($this->userId);

                        $productCard = $this->getProductCardFromRawData($productData);
                        $this->bot->sendProductCards($this->userId, [$productCard]);
                        die();
                    } else {
                        $this->bot->sorry($this->userId);
                    }
                } else {
                    $this->bot->wrongInput($this->userId);
                }
            } else {
                $this->bot->wrongInput($this->userId);
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
     * @throws \Kerox\Messenger\Exception\MessengerException
     */
    protected function sendWatchList(): void
    {
        //TODO: handle carousel card's max count
        // Up to 10 generic templates in one carousel
        // See eloquent batching
        $products = $this->getUserProducts();

        if ($products->isNotEmpty()) {
            $cards = [];

            foreach ($products as $product) {
                $cards[] = [
                    'bubble' => Product::isDiscountAvailable($product->raw_data) ? -1 : 1,
                    'payload' => $this->getProductCardFromRawData($product->raw_data, true)
                ];
            }

            if (count($cards)) {
                usort($cards, function ($a, $b) {
                    return $a['bubble'] <=> $b['bubble'];
                });

                $this->bot->sendProductCards($this->userId, array_column($cards, 'payload'));
            }
        } else {
            $this->bot->emptyWatchList($this->userId);
        }
    }

    /**
     * @param array $data
     * @param bool $isWatchlist
     * @return \Kerox\Messenger\Model\Message\Attachment\Template\Element\GenericElement
     * @throws \Kerox\Messenger\Exception\MessengerException
     */
    protected function getProductCardFromRawData(array $data, bool $isWatchlist = false): GenericElement
    {
        return $this->bot->getSingleProductCard(
            new Product($data),
            $isWatchlist
        );
    }

    /**
     * Get a user info
     *
     * @param int $uid
     * @return \Kerox\Messenger\Response\UserResponse
     * @throws \Kerox\Messenger\Exception\MessengerException
     */
    protected function getUserProfile(int $uid)
    {
        return $this->client->getUserProfile($uid);
    }

    /**
     * @return Collection
     */
    protected function getUserProducts(): Collection
    {
        return MessengerUser::getWatchlist($this->userId);
    }

    /**
     * @param \Kerox\Messenger\Response\UserResponse $user
     */
    protected function createOrUpdateUser($user)
    {
        //TODO: move to model
        $userModel = MessengerUser::firstOrNew(
            ['recipient_id' => $this->userId]
        );

        $userModel->recipient_id = $this->userId;
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
    protected function createOrUpdateWatchItem(array $item): int
    {
        //TODO: move to model
        $itemModel = MessengerWatchItem::firstOrNew([
            'recipient_id' => $this->userId,
            'product_id' => $item['api-handle']
        ]);

        $itemModel->recipient_id = $this->userId;
        $itemModel->product_id = $item['api-handle'] ?? null;
        $itemModel->name = $item['name'] ?? null;
        $itemModel->image = $item['image'] ?? null;
        $itemModel->store_url = $item['store-url'] ?? null;
        $itemModel->api_url = $item['api-url'] ?? null;
        $itemModel->prices = $item['prices'] ?? null;
        $itemModel->raw_data = $item ?? null;
        $itemModel->fetched_at = $item['fetched-at'] ?? null;
        $itemModel->save();

        return $itemModel->id;
    }

    /**
     * @param string $productId
     * @return mixed
     */
    public function removeItemFromWatchlist(string $productId)
    {
        return MessengerWatchItem::where('recipient_id', $this->userId)
            ->where('product_id', $productId)
            ->delete();
    }
}
