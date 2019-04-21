<?php
namespace App\PSN;


class StoreURL
{

    const STORE_API_PRODUCT_URL = 'https://store.playstation.com/valkyrie-api/%s/999/resolve/%s?depth=2';
    const STORE_FRONT_DOMAIN = 'store.playstation.com';
    const LOCALE_REG_EXP = '/[a-z]{2}-[a-z]{2}/';

    /**
     * @param string $url
     * @return bool
     */
    public function isStoreFrontURL(string $url): bool
    {
        return (false !== strpos($url, self::STORE_FRONT_DOMAIN));
    }
    /**
     * @param string $storeFrontUrl
     * @return string
     */
    public function getApiURL(string $storeFrontUrl): string
    {
        $locale = $this->getStoreFrontUriLocale($storeFrontUrl);
        $handle = $this->getStoreFrontUriHandle($storeFrontUrl);

        return $this->getProductApiUrl($locale, $handle);
    }

    /**
     * @param string $storeFrontUrl
     * @return string
     */
    public function getStoreFrontUriLocale(string $storeFrontUrl): string
    {
        $storeFrontUriArray = $this->getStoreFrontUriArray($storeFrontUrl);

        return (!empty($storeFrontUriArray[0])) ? $storeFrontUriArray[0] : '';
    }

    /**
     * @param string $storeFrontUrl
     * @return string
     */
    public function getStoreFrontUriHandle(string $storeFrontUrl): string
    {
        $storeFrontUriArray = $this->getStoreFrontUriArray($storeFrontUrl);

        return (!empty($storeFrontUriArray[2])) ? $storeFrontUriArray[2] : '';
    }

    /**
     * @param string $storeFrontUrl
     * @return array
     */
    protected function getStoreFrontUriArray(string $storeFrontUrl): array
    {
        $uriArray = [];
        $parsedUrl = parse_url($storeFrontUrl);

        if (!empty($parsedUrl['host']) && !empty($parsedUrl['path'])) {
            $pathArray = array_values(
                array_filter(explode('/', $parsedUrl['path']))
            );

            if (preg_match(self::LOCALE_REG_EXP, $pathArray[0]) && $pathArray[1] == 'product') {
                $uriArray = $pathArray;
            }
        }

        return $uriArray;
    }

    /**
     * @param string $locale
     * @return string
     */
    protected function getLocaleInApiFormat(string $locale): string
    {
        $apiLocale = '';
        $parts = explode('-', $locale);

        if (count($parts) == 2) {
            $apiLocale = sprintf("%s/%s", $parts[0], strtoupper($parts[1]));
        }

        return $apiLocale;
    }

    /**
     * @param string $locale
     * @param string $handle
     * @return string
     */
    protected function getProductApiUrl(string $locale, string $handle): string
    {
        $apiUrl = '';

        if (!empty($locale) && !empty($handle)) {
            if ($locale = $this->getLocaleInApiFormat($locale)) {
                $apiUrl = sprintf(self::STORE_API_PRODUCT_URL, $locale, $handle);
            }
        }

        return $apiUrl;
    }
}