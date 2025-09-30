<?php

namespace Slub\LisztCatalograisonne\Common;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MuscatConnection {

    const MUSCAT_URL = 'https://muscat.rism.info';
    const LOGIN_PATH = 'admin/login';
    const SOURCES_PATH = 'admin/sources';
    const MUSCAT_LOGINFORM_ID = 'session_new';

    protected $sessionCookie;
    protected $connection;
    protected $extConf;
    protected $url;

    public function __construct()
    {
		$this->extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('liszt_catalograisonne');
        $this->init();
    }

    protected function init()
    {
        $this->getAuthToken();
        $this->login();
    }

    protected function getAuthToken()
    {
        $parsedUrl = parse_url($this->extConf['mermeidUrl']);

        $this->url = self::MUSCAT_URL . '/' . self::LOGIN_PATH;
        if (isset($parsedUrl['path']) && $parsedUrl['path'] != "" && $parsedUrl['path'] != "/") {
            $this->path = $parsedUrl['path'];
        } else {
            $this->path = self::MUSCAT_URL;
        }

        $connection = curl_init($this->url);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        $resultstring = curl_exec($connection);
        var_dump($this->url);
        var_dump($resultstring);
        $document = new \DOMDocument();
        $document->loadHTML($resultstring);
        $xpath = new \DOMXPath($document);
        $query = '//' . self::MUSCAT_LOGINFORM_ID . '/[name="authenticity_token"]@val';
        var_dump($query);
        curl_close($connection);

        var_dump($result);

        //$this->sessionCookie = 
    }

    protected function login()
    {
        $parsedUrl = parse_url($this->extConf['mermeidUrl']);

        $url = self::MUSCAT_URL . '/' . self::LOGIN_PATH;
        if (isset($parsedUrl['path']) && $parsedUrl['path'] != "" && $parsedUrl['path'] != "/") {
            $this->path = $parsedUrl['path'];
        } else {
            $this->path = self::MUSCAT_URL;
        }
        $login = [
            'user[login]' => $this->extConf['rismUsername'],
            'user[password]' => $this->extConf['rismPassword']
        ];
        $loginString = http_build_query($login);

        $this->connection = curl_init($url);
        curl_setopt($this->connection, CURLOPT_POST, true);
        curl_setopt($this->connection, CURLOPT_POSTFIELDS, $loginString);
        curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($this->connection);
        var_dump($result);

        //$this->sessionCookie = 
    }

    public function getDocument(string $documentId): DOMDocument
    {
    }

    public function __destruct()
    {
        curl_close($this->connection);
    }
}
