<?php

declare(strict_types=1);

namespace Golden;

use PHPUnit\Framework\TestCase;

final class HelloSmokeTest extends TestCase
{
    public function testGetApiHelloReturns200(): void
    {
        $baseUrl = getenv('BASE_URL');
        if ($baseUrl === false || $baseUrl === '') {
            self::fail(
                'BASE_URL environment variable is not set. '
                . 'Example: BASE_URL=http://localhost:8080 vendor/bin/phpunit -c tests/phpunit.xml.dist'
            );
        }

        $url = rtrim($baseUrl, '/') . '/api/hello';
        $command = sprintf(
            'curl -s -o /dev/null -w "%%{http_code}" %s',
            escapeshellarg($url)
        );

        $statusCode = trim((string) shell_exec($command));

        self::assertSame(
            '200',
            $statusCode,
            sprintf('Expected HTTP 200 from %s, got %s', $url, $statusCode)
        );
    }
}
