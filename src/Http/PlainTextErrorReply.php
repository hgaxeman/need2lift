<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Frozen error contract (ADR-0002): text/plain body Not found or Error, status rules.
 */
final class PlainTextErrorReply
{
    public static function fromThrowable(Throwable $throwable): Response
    {
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        if ($throwable instanceof HttpExceptionInterface) {
            $status = $throwable->getStatusCode();
        }

        if ($status < 400) {
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $body = $status === Response::HTTP_NOT_FOUND ? 'Not found' : 'Error';

        return new Response(
            $body,
            $status,
            ['Content-Type' => 'text/plain; charset=utf-8']
        );
    }
}
