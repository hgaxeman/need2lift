<?php

declare(strict_types=1);

namespace Golden;

/**
 * Black-box HTTP helper for golden-file tests. Uses curl; no framework doubles.
 */
final class GoldenHttp
{
    /** @return array{status:int, contentType:string, body:string} */
    public static function request(string $method, string $path, array $headers = []): array
    {
        $baseUrl = getenv('BASE_URL');
        if ($baseUrl === false || $baseUrl === '') {
            throw new \RuntimeException(
                'BASE_URL environment variable is not set. '
                . 'Example: BASE_URL=http://localhost:8080 vendor/bin/phpunit -c tests/phpunit.xml.dist'
            );
        }

        $url = rtrim($baseUrl, '/') . $path;
        $headerFile = tempnam(sys_get_temp_dir(), 'golden-hdr');
        $bodyFile = tempnam(sys_get_temp_dir(), 'golden-body');
        if ($headerFile === false || $bodyFile === false) {
            throw new \RuntimeException('Unable to create temporary files for curl output');
        }

        $command = sprintf(
            'curl -sS -X %s -D %s -o %s',
            escapeshellarg($method),
            escapeshellarg($headerFile),
            escapeshellarg($bodyFile)
        );
        foreach ($headers as $name => $value) {
            $command .= ' -H ' . escapeshellarg($name . ': ' . $value);
        }
        $command .= ' ' . escapeshellarg($url);

        $output = [];
        $exitCode = 0;
        exec($command . ' 2>&1', $output, $exitCode);
        if ($exitCode !== 0) {
            @unlink($headerFile);
            @unlink($bodyFile);
            throw new \RuntimeException(
                'curl failed (' . $exitCode . '): ' . implode("\n", $output)
            );
        }

        $rawHeaders = (string) file_get_contents($headerFile);
        $body = (string) file_get_contents($bodyFile);
        @unlink($headerFile);
        @unlink($bodyFile);

        if (!preg_match('/^HTTP\/\S+\s+(\d+)/', $rawHeaders, $matches)) {
            throw new \RuntimeException('Unable to parse HTTP status from curl headers');
        }
        $status = (int) $matches[1];

        $contentType = '';
        if (preg_match('/^Content-Type:\s*(.+)$/mi', $rawHeaders, $typeMatches)) {
            $contentType = trim($typeMatches[1]);
        }

        return [
            'status' => $status,
            'contentType' => $contentType,
            'body' => $body,
        ];
    }

    /** @param array{status:int, contentType:string, body:string} $response */
    public static function normalise(array $response): string
    {
        $body = preg_replace(
            '/Hello from PHP \d+\.\d+\.\d+/',
            'Hello from PHP \\d+\\.\\d+\\.\\d+',
            $response['body']
        );
        if ($body === null) {
            $body = $response['body'];
        }

        return sprintf(
            "%d\n%s\n%s\n",
            $response['status'],
            $response['contentType'],
            $body
        );
    }

    public static function goldenPath(string $name): string
    {
        return dirname(__DIR__) . '/golden/' . $name . '.golden';
    }

    public static function isRecording(): bool
    {
        return getenv('GOLDEN_RECORD') === '1';
    }

    public static function assertMatchesGolden(string $name, string $actual): void
    {
        $path = self::goldenPath($name);
        if (self::isRecording()) {
            $dir = dirname($path);
            if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException('Unable to create golden directory: ' . $dir);
            }
            if (file_put_contents($path, $actual) === false) {
                throw new \RuntimeException('Unable to write golden file: ' . $path);
            }

            return;
        }

        if (!is_file($path)) {
            throw new \RuntimeException(
                'Golden file missing: ' . $path . '. Record with GOLDEN_RECORD=1'
            );
        }

        $expected = (string) file_get_contents($path);
        if ($actual !== $expected) {
            throw new \RuntimeException(
                "Golden mismatch for {$name}.\n--- expected ---\n{$expected}\n--- actual ---\n{$actual}"
            );
        }
    }
}
