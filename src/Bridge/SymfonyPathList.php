<?php

declare(strict_types=1);

namespace App\Bridge;

/**
 * Exact, case-sensitive path matcher for the Zend→Symfony bridge (ADR-0009).
 * The query string is ignored; only the path is compared to the list.
 */
final class SymfonyPathList
{
    /** @var list<string> */
    private $paths;

    /** @param list<string> $paths */
    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    public function matches(string $requestUri): bool
    {
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
