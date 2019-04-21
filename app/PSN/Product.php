<?php
namespace App\PSN;


use GuzzleHttp\Client;

class Product
{

    /**
     * @var \GuzzleHttp\Client
     */
    protected $http;

    /**
     * Product constructor.
     * @param \GuzzleHttp\Client $client
     */
    public function __construct(Client $client)
    {
        $this->http = $client;
    }

    /**
     * @param string $apiUrl
     * @param string $handle
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getApiProductData(string $apiUrl, string $handle)
    {
        $product = [];

        if (!empty($apiUrl)) {
            $data = $this->request($apiUrl);
            $product = [];

            if ($data['included']) {
                foreach ($data['included'] as $included) {
                    if ($included['id'] == $handle) {
                        $product = $this->parseProductData($included['attributes']);
                        $product['api-handle'] = $handle;
                        $product['api-url'] = $apiUrl;
                        $product['fetched-at'] = time();
                        break;
                    }
                }
            }
        }

        return $product;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function parseProductData(array $data): array
    {
        $product = [];

        if ($defaultSku = (!empty($data['default-sku-id'])) ? $data['default-sku-id'] : null) {
            $product['name'] = $data['name'];
            $product['image'] = $data['thumbnail-url-base'];

            foreach ($data['skus'] as $sku) {
                if ($sku['id'] == $defaultSku) {
                    $product['prices'] = $sku['prices'];
                    break;
                }
            }
        }

        return $product;
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $params
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request(string $url, string $method = 'GET', array $params = [])
    {
        $response = $this->http->request($method, $url, $params);

        if ($response->getStatusCode() == 200) {
            return json_decode($response->getBody()->getContents(), true);
        }

        return [];
    }
}