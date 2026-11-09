<?php

declare(strict_types=1);

namespace Golden;

use PHPUnit\Framework\TestCase;

/**
 * Proves the default image (no test overlay) does not expose the failure route.
 */
final class DispatchFailureGuardTest extends TestCase
{
    public function testDispatchFailureRouteAbsentOnDefaultImage(): void
    {
        if (getenv('GOLDEN_FAILURE_ROUTE') === '1') {
            self::markTestSkipped(
                'Guard runs against the default Compose image only (unset GOLDEN_FAILURE_ROUTE)'
            );
        }

        try {
            $response = GoldenHttp::request('GET', '/test/dispatch-failure');
            $normalised = GoldenHttp::normalise($response);
            GoldenHttp::assertMatchesGolden('GET.test-dispatch-failure.absent', $normalised);
        } catch (\RuntimeException $exception) {
            self::fail($exception->getMessage());
        }

        self::assertTrue(true);
    }
}
