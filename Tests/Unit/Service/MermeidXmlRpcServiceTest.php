<?php

namespace Slub\LisztCatalograisonne\Tests\Unit\Service;

use Slub\LisztCatalograisonne\Services\MermeidXmlRpcService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class MermeidXmlRpcServiceTest extends UnitTestCase
{
    protected ?MermeidXmlRpcService $subject = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new MermeidXmlRpcService();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /*
     * @test
     */
    protected function documentCanBeStoredAndRetrievedFromMermeidInstance(): void
    {
        $this->subject->getDocument('');
    }
}
