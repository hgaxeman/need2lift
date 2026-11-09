<?php

declare(strict_types=1);

namespace Bridge;

use PHPUnit\Framework\TestCase;

/**
 * Proves /api/hello is answered by Symfony (internal header), not Zend.
 * The header is not part of any golden file.
 */
final class SymfonyHelloServedTest extends TestCase
{
    public function testApiHelloSetsSymfonyEngineHeader(): void
    {
        $baseUrl = getenv('BASE_URL');
        if ($baseUrl === false || $baseUrl === '') {
            self::fail(
                'BASE_URL environment variable is not set. '
                . 'Example: BASE_URL=http://localhost:8080 vendor/bin/phpunit -c tests/phpunit.xml.dist'
            );
        }

        $url = rtrim($baseUrl, '/') . '/api/hello';
        $headerFile = tempnam(sys_get_temp_dir(), 'symfony-hdr');
        if ($headerFile === false) {
            self::fail('Unable to create temporary file for curl headers');
        }

        $command = sprintf(
            'curl -sS -D %s -o /dev/null %s',
            escapeshellarg($headerFile),
            escapeshellarg($url)
        );
        $output = [];
        $exitCode = 0;
        exec($command . ' 2>&1', $output, $exitCode);
        $rawHeaders = (string) file_get_contents($headerFile);
        @unlink($headerFile);

        if ($exitCode !== 0) {
            self::fail('curl failed (' . $exitCode . '): ' . implode("\n", $output));
        }

        self::assertMatchesRegularExpression(
            '/^X-Need2lift-Engine:\s*symfony\s*$/mi',
            $rawHeaders,
            'Expected X-Need2lift-Engine: symfony on /api/hello'
        );
    }
}
