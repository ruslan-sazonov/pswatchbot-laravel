<?php
namespace App\PSN;


class Store
{

    /**
     * @var \App\PSN\StoreURL
     */
    protected $urlHelper;

    /**
     * @var \App\PSN\Product
     */
    protected $product;

    /**
     * Store constructor.
     * @param \App\PSN\StoreURL $urlHelper
     * @param \App\PSN\Product $productData
     */
    public function __construct(StoreURL $urlHelper, Product $productData)
    {
        $this->urlHelper = $urlHelper;
        $this->product = $productData;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function isValidStoreFrontURL(string $url): bool
    {
        return $this->urlHelper->isStoreFrontURL($url);
    }

    /**
     * @param string $storeFrontUrl
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getGameDataByURL(string $storeFrontUrl): array
    {
        $gameData = [];

        if (!empty($storeFrontUrl)) {
            $apiUrl = $this->urlHelper->getApiURL($storeFrontUrl);
            $apiHandle = $this->urlHelper->getStoreFrontUriHandle($storeFrontUrl);
            $gameData = $this->product->getApiProductData($apiUrl, $apiHandle);
            $gameData['store-url'] = $storeFrontUrl;
        }

        return $gameData;
    }
}