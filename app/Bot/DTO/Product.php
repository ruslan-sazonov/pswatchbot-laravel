<?php


namespace App\Bot\DTO;

use App\Bot\Interfaces\DTO\ProductInterface;
use Spatie\Emoji\Emoji;

class Product implements ProductInterface
{

    /**
     * @var array $product
     */
    protected $product;

    protected $isDiscountAvailable = false;

    public function __construct(array $product)
    {
        //TODO: move DTO to PSN namespace
        $this->product = $product;
        $this->isDiscountAvailable = (bool) $this->product['prices']['non-plus-user']['discount-percentage'];

//        var_dump($product);die();
    }

    public function getTitleAsText(): string
    {
        $title = '';

        if ($this->isDiscountAvailable) {
            $title = Emoji::fire() . ' ';
        }

        $title .= $this->product['name'] ?? '';

        return $title;
    }

    public function getPricesAsText(): string
    {
        $priceNowStr = 'Price Now: %s';
        $priceWasStr = 'Price Was: %s';
        $discountStr = 'Discount: %s%%';
        $finalStr = '';

        $discount = $this->product['prices']['non-plus-user']['discount-percentage'] ?? false;

        $priceWasText = false;
        $discountText = false;
        $priceNowText = sprintf($priceNowStr, $this->product['prices']['non-plus-user']['actual-price']['display'] ?? 'n/a');

        if ($discount) {
            $discountText = sprintf($discountStr, $discount);
            $priceWasText = sprintf($priceWasStr, $this->product['prices']['non-plus-user']['strikethrough-price']['display'] ?? 'n/a');
            $finalStr = $priceNowText . PHP_EOL . $priceWasText . PHP_EOL . $discountText;
        } else {
            $finalStr = $priceNowStr;
        }

        //TODO: format prices with discounts and PS+

        return $finalStr;
    }

    public function getImageUrl(): string
    {
        return $this->product['image'] ?? '';
    }

    public function getStoreUrl(): string
    {
        return $this->product['store-url'] ?? '';
    }
}