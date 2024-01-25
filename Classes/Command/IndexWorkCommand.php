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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Slub\LisztCommon\Common\ElasticClientBuilder;
use Slub\LisztCatalograisonne\Common\MermeidConnection;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IndexWorkCommand extends Command
{

    const ROOT_ID = 'main_content';

    protected $extConf;
    protected $client;
    protected $io;
    protected $document;
    protected $filename;
    protected $url;

    protected function configure(): void
    {
        $this->setDescription('Create elasticsearch index from work document');
        $this->addArgument('filename', InputArgument::REQUIRED, 'name of file to be indexed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->section('Fetching Document');
        $this->fetchDocument();
        $this->io->section('Committing Document');
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
        $folder = $defaultStorage->getFolder($this->extConf['mermeidFileFolder']);
        $this->document = $folder->getFile($this->filename)->getContents();
    }

    protected function commitDocument(): void
    {
        $index = $this->extConf['elasticWorkIndexName'];
        $params = [
            'index' => $index,
            'id' => str_replace('.json', '', $this->filename),
            'body' => [
                'content' => $this->document
            ]
        ];
        $this->client->index($params);
    }
}
