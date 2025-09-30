<?php

declare(strict_types=1);

/*
 * This file is part of the Liszt Catalog Raisonne project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 */

namespace Slub\LisztCatalograisonne\Command;

use Elasticsearch\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Slub\LisztCommon\Common\ElasticClientBuilder;
use Slub\LisztCommon\Common\XMLElement;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;

class IndexSourceCommand extends Command
{

    const DEFAULT_PATH = 'modules/present.xq'; 
    const DEFAULT_SCHEMA = 'https';
    const DEFAULT_HOST = 'localhost';
    const DEFAULT_PORT = 8080;

    protected ExtensionConfiguration $extConf;
    protected Client $client;
    protected SymfonyStyle $io;
    protected $document;
    protected string $rismId;
    protected string $url;
    protected string $filename;
    protected array $parsedDocument;

    protected function configure(): void
    {
        $this->setDescription('Create elasticsearch index from work document');
        $this->addArgument('filename', InputArgument::REQUIRED, 'name of the file to be indexed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->section('Fetching Document');
        $this->fetchDocument();
        $this->parseDocument();
        $this->io->section('Committing Bibliography Data');
        $this->commitDocument();
        return 0;
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title($this->getDescription());

		$this->extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('liszt_catalograisonne');
        $this->filename = $input->getArgument('filename');
        $this->client = ElasticClientBuilder::getClient();
    }

    protected function fetchDocument(): void
    {
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $defaultStorage = $storageRepository->getDefaultStorage();
        $folder = $defaultStorage->getFolder($this->extConf['marcFileFolder']);
        $xml = $folder->getFile($this->filename)->getContents();

        $this->document = simplexml_load_string($xml, \SimpleXMLElement::class, 0, 'marc', true);
    }

    protected function parseDocument(): void
    {
        foreach($this->document->record->controlfield as $field) {
            foreach($field->attributes() as $attribute) {
                if ($attribute == '001')
                    $this->rismId = $field->__toString();
            }
        }
        $this->parsedDocument = [];
        foreach ($this->document->record->datafield as $field) {
            foreach ($field->attributes() as $key => $value) {
                if ($key == 'tag') {
                    if (!isset($this->parsedDocument[$value->__toString()])) {
                        $this->parsedDocument[$value->__toString()] = [];
                    }
                    $fieldvalue = [];
                    foreach($field as $subfield) {
                        foreach ($subfield->attributes() as $subkey => $subvalue) {
                            if ($subkey == 'code') {
                                if (!isset($fieldvalue[$subvalue->__toString()])) {
                                    $fieldvalue[$subvalue->__toString()] = [];
                                }
                                $fieldvalue[$subvalue->__toString()][] = $subfield;
                            }
                        }
                    }
                    $this->parsedDocument[$value->__toString()][] = $fieldvalue;
                }
            }
        }
    }

    protected function commitDocument(): void
    {
        $jsonDocument = json_encode($this->parsedDocument);
        $index = $this->extConf['elasticSourceIndexName'];
        $params = [
            'index' => $index,
            'id' => $this->rismId,
            'body' => $jsonDocument
        ];
        $this->client->index($params);
    }
}
