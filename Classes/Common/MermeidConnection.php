<?php

namespace Slub\LisztCatalograisonne\Common;

/*
 * This file is part of the Liszt Catalog Raisonne project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

class MermeidConnection {

    const DEFAULT_PATH = 'modules/present.xq'; 
    const DEFAULT_SCHEMA = 'http';
    const DEFAULT_HOST = 'localhost';
    const DEFAULT_PORT = 8080;

    protected $host;
    protected $port;
    protected $path;
    protected $schema;

    public function __construct()
    {
		$extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('liszt_catalograisonne');

        $parsedUrl = parse_url($extConf['mermeidUrl']);

        if (isset($parsedUrl['path']) && $parsedUrl['path'] != "" && $parsedUrl['path'] != "/") {
            $this->path = $parsedUrl['path'];
        } else {
            $this->path = self::DEFAULT_PATH;
        }
        $this->host = $parsedUrl['host'] ?? self::DEFAULT_HOST;
        $this->port = $parsedUrl['port'] ?? self::DEFAULT_PORT;
        $this->schema = $parsedUrl['schema'] ?? self::DEFAULT_SCHEMA;
    }

    public function getDocument(string $documentId): \DOMDocument
    {
        $query = http_build_query(['doc' => $documentId]);
        $url = $this->schema . '://' . 
            $this->host . ':' . 
            $this->port . '/' . 
            $this->path . '?' . $query;

        $connection = curl_init($url);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        $resultstring = curl_exec($connection);

        if (!$resultstring)
            throw new \ErrorException('connection to mermeid failed');
        curl_close($connection);

        $document = new \DOMDocument();
        $document->loadHTML($resultstring);
        return $document;
    }
}
