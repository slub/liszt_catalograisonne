<?php

namespace Slub\LisztCatalograisonne\Services;

interface MermeidServiceInterface
{
    public function getDocument(string $filename): string;

    public function storeDocument(string $filename, string $document): void;
}
