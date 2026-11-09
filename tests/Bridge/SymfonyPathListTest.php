<?php

declare(strict_types=1);

namespace Bridge;

use App\Bridge\SymfonyPathList;
use PHPUnit\Framework\TestCase;

/**
 * ADR-0009: exact, case-sensitive path match; query string stripped before compare.
 * The production list is empty until p2-03; this fixture uses /api/hello as that entry.
 */
final class SymfonyPathListTest extends TestCase
{
    private SymfonyPathList $list;

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

    public function testEmptyProductionListMatchesNothing(): void
    {
        $paths = require dirname(__DIR__, 2) . '/config/symfony_bridge_paths.php';
        self::assertSame([], $paths);

        $empty = new SymfonyPathList($paths);
        self::assertFalse($empty->matches('/api/hello'));
        self::assertFalse($empty->matches('/api/hello?x=1'));
        self::assertFalse($empty->matches('/nope'));
    }
}
