<?php


namespace App\Bot\Interfaces\DTO;


interface ProductInterface
{

    public function getTitleAsText(): string;

    public function getPricesAsText(): string;

    public function getImageUrl(): string;

    public function getStoreUrl(): string;
}