<?php


namespace App\PSN\DTO;

use App\PSN\Interfaces\DTO\ProductInterface;
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
        $this->product = $product;
        $this->isDiscountAvailable = self::isDiscountAvailable($this->product);
    }

    public static function isDiscountAvailable(array $data): bool
    {
        return (bool) $data['prices']['non-plus-user']['discount-percentage'];
    }

    public function getId(): string
    {
        return $this->product['api-handle'];
    }

    public function getTitleAsText(): string
    {
        return $this->product['name'];
    }

    public function getPricesAsText(): string
    {
        $priceNowStr = 'Price Now: %s';
        $priceWasStr = 'Price Was: %s';
        $discountStr = 'Discount: %s%% %s';
        $finalText = '';

        $discount = $this->product['prices']['non-plus-user']['discount-percentage'] ?? false;

        $priceWasText = false;
        $discountText = false;
        $priceNowText = sprintf($priceNowStr, ($this->product['prices']['non-plus-user']['actual-price']['display'] ?? 'n/a'));

        if ($discount) {
            $discountText = sprintf($discountStr, $discount, Emoji::fire());
            $priceWasText = sprintf($priceWasStr, ($this->product['prices']['non-plus-user']['strikethrough-price']['display'] ?? 'n/a'));
            $finalText = $priceNowText . PHP_EOL . $priceWasText . PHP_EOL . $discountText;
        } else {
            $finalText = $priceNowText;
        }

        //TODO: format prices with discounts and PS+

        return $finalText;
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