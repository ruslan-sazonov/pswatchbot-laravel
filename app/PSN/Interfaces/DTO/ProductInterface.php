<?php


namespace App\PSN\Interfaces\DTO;


interface ProductInterface
{

    public function getId(): string;

    public function getTitleAsText(): string;

    public function getPricesAsText(): string;

    public function getImageUrl(): string;

    public function getStoreUrl(): string;
}