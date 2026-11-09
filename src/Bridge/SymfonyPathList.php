<?php

declare(strict_types=1);

namespace App\Bridge;

/**
 * Exact, case-sensitive path matcher for the Symfony bridge list (ADR-0009).
 * The query string is ignored; only the path is compared to the list.
 * The sentinel "*" matches every path (Symfony answers all HTTP requests).
 */
final class SymfonyPathList
{
    public const CATCH_ALL = '*';

    /** @var list<string> */
    private $paths;

    /** @param list<string> $paths */
    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    public function matches(string $requestUri): bool
    {
        if (in_array(self::CATCH_ALL, $this->paths, true)) {
            return true;
        }

        $path = parse_url($requestUri, PHP_URL_PATH);
        if (!is_string($path)) {
            return false;
        }

        return in_array($path, $this->paths, true);
    }

    /** @return list<string> */
    public function paths(): array
    {
        return $this->paths;
    }
}
