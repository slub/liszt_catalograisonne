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

namespace Slub\LisztCatalograisonne\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Slub\LisztCommon\Controller\ClientEnabledController;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class WorkController extends ClientEnabledController
{
    /** id of list target **/
    const MAIN_TARGET = 'works';

    /** @var ResponseFactoryInterface */
    protected $responseFactory;

    /** @var StreamFactoryInterface */
    protected $streamFactory;

    protected string $jsCall;

    protected string $div;

    public function __construct(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
    }

    public function indexAction(): ResponseInterface
    {
        $this->createJsCall();
        $this->wrapTargetDiv();
        $contentStream = $this->
            streamFactory->
            createStream(
                $this->div . 
                $this->jsCall
            );

        return $this->
            responseFactory->
            createResponse()->
            withBody($contentStream);
    }

    private function wrapTargetDiv(): void
    {
        $mainCol =  '<div id="' .
            self::MAIN_TARGET .
            '" class="col-md order-md-1"></div>';
        $this->div = '<div class="container"><div class="row">' .
            $mainCol . '</div>';
    }

    private function createJsCall() {
        $this->jsCall =
            '<script>' .
                'document.addEventListener("DOMContentLoaded", _ => {' .
                    'workController = new WorkController({' .
                        'target:"' . self::MAIN_TARGET .
                    '"});' .
                    'workController.init().then(_ =>' .
                        'workController.listAction()' .
                    ');' .
                '});' .
            '</script>';
    }
}
