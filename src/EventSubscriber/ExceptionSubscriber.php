<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
    $exception = $event->getThrowable();
        if ($exception instanceof HttpException) {
            $data = [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage()
            ];

            if ($exception->getStatusCode() === 403) {
                $data = [
                    'status' => $exception->getStatusCode(),
                    'message' => "Vous n'avez pas les droits nécessaires."
                ];
            }

            if ($exception->getStatusCode() === 404) {
                $data = [
                    'status' => $exception->getStatusCode(),
                    'message' => "L'objet que vous cherché n'a pas été trouvé"
                ];
            }
            $event->setResponse(new JsonResponse($data));
        } else {
            $data = [
                'status' => 500, // Le status n'existe pas car ce n'est pas une exception HTTP, donc on met 500 par défaut.
                'message' => $exception->getMessage()
            ];
                $event->setResponse(new JsonResponse($data));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
