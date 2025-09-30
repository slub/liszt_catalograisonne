<?php

namespace Slub\LisztCatalograisonne\Services;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MermeidXmlRpcService implements MermeidServiceInterface
{

    const DATA_PATH = '/db/apps/mermeid-data/';
    const XMLRPC_PATH = 'xmlrpc/';
    const GET_DOCUMENT_PARAMETERS = [
            'indent' => 'yes',
            'encoding' => 'UTF-8',
            'omit-xml-declaration' => 'no',
            'expand-xincludes' => 'no',
            'process-xsl-pi' => 'no',
            'highlight-matches' => 'no'
        ];
    const HTTP_HEADER = [
        'Content-Type: text/xml'
    ];

    protected string $url;
    protected $handle;

    public function init(): bool
    {
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('liszt_catalograisonne');
        $this->url = $extConf['mermeidUrl'] . self::XMLRPC_PATH;

        $this->handle = curl_init($this->url);
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, self::HTTP_HEADER);
        curl_setopt($this->handle, CURLOPT_HEADER, 0);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_POST, true);

        return true;
    }

    public function getDocument(string $filename): string
    {
        $post = xmlrpc_encode_request('listMethods', []);
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $post);
        var_dump(curl_exec($this->handle));die;

        $params = [ self::DATA_PATH . $filename, self::GET_DOCUMENT_PARAMETERS ];
        $post = xmlrpc_encode_request('getDocument', $params);
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $post);

        return xmlrpc_decode(curl_exec($this->handle))->scalar;
    }

    public function storeDocument(string $filename, string $document): void
    {
        $params = [ $document, self::DATA_PATH . $filename, 1 ];
        $post = xmlrpc_encode_request('parse', $params);
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $post);
        curl_exec($this->handle);
    }

    public function deleteDocument(string $filename): void
    {
        $params = [ self::DATA_PATH . $filename, self::GET_DOCUMENT_PARAMETERS ];
        $post = xmlrpc_encode_request('getDocument', $params);
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $post);
    }

}
