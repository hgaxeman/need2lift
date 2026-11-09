<?php

declare(strict_types=1);

namespace App\Controller;

use RuntimeException;

/**
 * Used only when the Compose test overlay mounts the failure route.
 * Throws so PlainTextExceptionListener returns 500 Error.
 */
final class TestDispatchFailureController
{
    public function fail(): void
    {
        throw new RuntimeException('intentional dispatch failure');
    }
}
