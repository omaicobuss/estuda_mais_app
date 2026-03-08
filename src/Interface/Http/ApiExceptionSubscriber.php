<?php

declare(strict_types=1);

namespace App\Interface\Http;

use App\Application\ApiException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private KernelInterface $kernel)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $throwable = $event->getThrowable();
        if ($throwable instanceof ApiException) {
            $event->setResponse(new JsonResponse([
                'error' => $throwable->getMessage(),
            ], $throwable->statusCode()));

            return;
        }

        $event->setResponse(new JsonResponse([
            'error' => 'Erro interno.',
            'details' => $this->kernel->isDebug() ? $throwable->getMessage() : null,
        ], 500));
    }
}
