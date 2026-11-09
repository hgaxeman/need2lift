<?php

declare(strict_types=1);

namespace Golden;

use PHPUnit\Framework\TestCase;

/**
 * Requires the Compose test overlay (GOLDEN_FAILURE_ROUTE=1).
 * Recorded against /test/dispatch-failure → 500 Error.
 */
final class DispatchFailureGoldenTest extends TestCase
{
    public function testDispatchFailureMatchesGolden(): void
    {
        if (getenv('GOLDEN_FAILURE_ROUTE') !== '1') {
            self::markTestSkipped(
                'Set GOLDEN_FAILURE_ROUTE=1 and run with docker-compose.test.yml overlay'
            );
        }

        try {
            $response = GoldenHttp::request('GET', '/test/dispatch-failure');
            $normalised = GoldenHttp::normalise($response);
            GoldenHttp::assertMatchesGolden('GET.test-dispatch-failure', $normalised);
        } catch (\RuntimeException $exception) {
            self::fail($exception->getMessage());
        }

        self::assertTrue(true);
    }
}
