<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class XHRAuthenticationExpiredExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $request = $event->getRequest();

        $format = $request->getRequestFormat();
        $exception = $event->getThrowable();

        if (!$request->isXmlHttpRequest()) {
            return;
        }

        if (!$exception instanceof AuthenticationException && !$exception instanceof AccessDeniedException) {
            return;
        }

        if ('json' === $format) {
            $response = new JsonResponse(['message' => 'Login expired'], 403);
            $event->setResponse($response);

            return;
        }

        $response = new Response('Login expired', 403);
        $event->setResponse($response);
    }
}
