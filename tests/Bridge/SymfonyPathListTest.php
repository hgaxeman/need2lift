<?php

declare(strict_types=1);

namespace Bridge;

use App\Bridge\SymfonyPathList;
use PHPUnit\Framework\TestCase;

/**
 * ADR-0009: exact, case-sensitive path match; query string stripped before compare.
 */
final class SymfonyPathListTest extends TestCase
{
    /** @var SymfonyPathList */
    private $list;

    protected function setUp(): void
    {
        $this->list = new SymfonyPathList(['/api/hello']);
    }

    public function testApiHelloIsOnTheList(): void
    {
        self::assertSame(['/api/hello'], $this->list->paths());
        self::assertTrue($this->list->matches('/api/hello'));
    }

    public function testTrailingSlashDoesNotMatch(): void
    {
        self::assertFalse($this->list->matches('/api/hello/'));
    }

    public function testDifferentCaseDoesNotMatch(): void
    {
        self::assertFalse($this->list->matches('/API/HELLO'));
    }

    public function testQueryStringIsStrippedBeforeMatch(): void
    {
        self::assertTrue($this->list->matches('/api/hello?x=1'));
    }

    public function testProductionListContainsApiHelloOnly(): void
    {
        $paths = require dirname(__DIR__, 2) . '/config/symfony_bridge_paths.php';
        self::assertSame(['/api/hello'], $paths);

        $production = new SymfonyPathList($paths);
        self::assertTrue($production->matches('/api/hello'));
        self::assertTrue($production->matches('/api/hello?x=1'));
        self::assertFalse($production->matches('/api/hello/'));
        self::assertFalse($production->matches('/API/HELLO'));
        self::assertFalse($production->matches('/nope'));
    }
}
