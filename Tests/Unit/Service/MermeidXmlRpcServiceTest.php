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

    /**
     * @test
     */
    public function documentCanBeStoredAndRetrievedFromMermeidInstance(): void
    {
        $this->markTestIncomplete('MermeidXmlRpcService::getDocument() is unfinished (dead code after debug var_dump/die, never calls init()) and needs a live Mermeid instance to test against.');
    }
}
