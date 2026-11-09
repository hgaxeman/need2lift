<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Http\PlainTextErrorReply;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Maps every kernel exception to the frozen text/plain Not found / Error replies (ADR-0002).
 */
final class PlainTextExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $event->setResponse(PlainTextErrorReply::fromThrowable($event->getThrowable()));
    }
}
