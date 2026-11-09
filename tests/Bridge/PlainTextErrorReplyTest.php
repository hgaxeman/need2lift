<?php

declare(strict_types=1);

namespace Bridge;

use App\Http\PlainTextErrorReply;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * ADR-0002 status rule: keep status >= 400, force below 400 to 500;
 * body Not found for 404, Error otherwise.
 */
final class PlainTextErrorReplyTest extends TestCase
{
    public function testHttpException418KeepsStatusAndErrorBody(): void
    {
        $response = PlainTextErrorReply::fromThrowable(new HttpException(418, 'teapot'));

        self::assertSame(418, $response->getStatusCode());
        self::assertSame('Error', $response->getContent());
        self::assertSame('text/plain; charset=utf-8', $response->headers->get('Content-Type'));
    }

    public function testExceptionWithoutStatusBecomes500Error(): void
    {
        $response = PlainTextErrorReply::fromThrowable(new RuntimeException('boom'));

        self::assertSame(500, $response->getStatusCode());
        self::assertSame('Error', $response->getContent());
        self::assertSame('text/plain; charset=utf-8', $response->headers->get('Content-Type'));
    }

    public function testStatus404GivesNotFoundBody(): void
    {
        $response = PlainTextErrorReply::fromThrowable(new NotFoundHttpException('missing'));

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Not found', $response->getContent());
        self::assertSame('text/plain; charset=utf-8', $response->headers->get('Content-Type'));
    }

    public function testStatusBelow400ForcedTo500(): void
    {
        $response = PlainTextErrorReply::fromThrowable(new HttpException(302, 'redirect'));

        self::assertSame(500, $response->getStatusCode());
        self::assertSame('Error', $response->getContent());
    }
}
