<?php

declare(strict_types=1);

namespace Golden;

use PHPUnit\Framework\TestCase;

final class ContractGoldenTest extends TestCase
{
    /**
     * @dataProvider publicContractCases
     *
     * @param array<string, string> $headers
     */
    public function testPublicContractMatchesGolden(
        string $goldenName,
        string $method,
        string $path,
        array $headers
    ): void {
        try {
            $response = GoldenHttp::request($method, $path, $headers);
            $normalised = GoldenHttp::normalise($response);
            GoldenHttp::assertMatchesGolden($goldenName, $normalised);
        } catch (\RuntimeException $exception) {
            self::fail($exception->getMessage());
        }

        self::assertTrue(true);
    }

    /** @return iterable<string, array{0:string,1:string,2:string,3:array<string,string>}> */
    public function publicContractCases(): iterable
    {
        yield 'GET /api/hello' => ['GET.api-hello', 'GET', '/api/hello', []];
        yield 'POST /api/hello' => ['POST.api-hello', 'POST', '/api/hello', []];
        yield 'PUT /api/hello' => ['PUT.api-hello', 'PUT', '/api/hello', []];
        yield 'DELETE /api/hello' => ['DELETE.api-hello', 'DELETE', '/api/hello', []];
        yield 'OPTIONS /api/hello' => ['OPTIONS.api-hello', 'OPTIONS', '/api/hello', []];
        yield 'GET /api/hello?x=1' => ['GET.api-hello.query', 'GET', '/api/hello?x=1', []];
        yield 'GET /api/hello Accept json' => [
            'GET.api-hello.accept-json',
            'GET',
            '/api/hello',
            ['Accept' => 'application/json'],
        ];
        yield 'GET /api/hello/' => ['GET.api-hello.trailing-slash', 'GET', '/api/hello/', []];
        yield 'POST /api/hello/' => ['POST.api-hello.trailing-slash', 'POST', '/api/hello/', []];
        yield 'GET /API/HELLO' => ['GET.API-HELLO', 'GET', '/API/HELLO', []];
        yield 'POST /API/HELLO' => ['POST.API-HELLO', 'POST', '/API/HELLO', []];
        yield 'GET /nope' => ['GET.nope', 'GET', '/nope', []];
        yield 'POST /nope' => ['POST.nope', 'POST', '/nope', []];
    }
}
