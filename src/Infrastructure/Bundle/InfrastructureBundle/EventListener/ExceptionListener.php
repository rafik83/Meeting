<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Templating\EngineInterface;

class ExceptionListener
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var EngineInterface */
    private $templating;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    const TEMPLATE_MAIN_ERRORS = [
        Response::HTTP_INTERNAL_SERVER_ERROR => 'TwigBundle:Exception:error500.html.twig',
        Response::HTTP_NOT_FOUND             => 'TwigBundle:Exception:error404.html.twig',
        Response::HTTP_FORBIDDEN             => 'TwigBundle:Exception:error403.html.twig',
    ];

    const TEMPLATE_EVENT_ERRORS = [
        Response::HTTP_NOT_FOUND => 'EventBundle:Exception:error404.html.twig',
        Response::HTTP_FORBIDDEN => 'EventBundle:Exception:error403.html.twig',
    ];

    public function __construct(
        EventRepositoryInterface $eventRepository,
        EngineInterface $templating,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->eventRepository             = $eventRepository;
        $this->templating                  = $templating;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    public function onKernelException(GetResponseForExceptionEvent $responseForExceptionEvent)
    {
        $request   = $responseForExceptionEvent->getRequest();
        $exception = $responseForExceptionEvent->getException();

        /*
         * Symfony throw a redirect when User is not logged
         * and there is AuthenticationException or AccessDeniedException
         * See Symfony\Component\Security\Http\Firewall\ExceptionListener
         */
        if (($exception instanceof AuthenticationException || $exception instanceof AccessDeniedException)
            && !$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
        ) {
            return;
        }

        /*
         * Try to get the event matching the host.
         */
        try {
            $event = $this->eventRepository->getEventByDomain($request->getHost());
        } catch (\Exception $exception) {
            return;
        }

        if ($event instanceof Event && !$event->isVisible()) {
            return;
        }

        $statusCode = $this->resolveHttpStatusCode($exception);

        $responseForExceptionEvent->setResponse(
            $this->buildResponseFromHttpStatusCode($statusCode, $event, $request, $exception->getMessage())
        );

        $responseForExceptionEvent->stopPropagation();
    }

    /**
     * @param \Exception $exception
     *
     * @return int one of 403, 404 or 500
     */
    private function resolveHttpStatusCode(\Exception $exception): int
    {
        $statusCode = $exception->getCode();

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        }

        if (!in_array($statusCode, [Response::HTTP_FORBIDDEN, Response::HTTP_NOT_FOUND])) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $statusCode;
    }

    private function buildResponseFromHttpStatusCode(int $statusCode, Event $event = null, Request $request, string $statusText = null): Response
    {
        if (null !== $event && Response::HTTP_INTERNAL_SERVER_ERROR !== $statusCode) {
            $request->setLocale($event->getAvailableLocale($request->getLocale()));

            return new Response(
                $this->templating->render(self::TEMPLATE_EVENT_ERRORS[$statusCode], ['event' => $event]),
                $statusCode
            );
        }

        return new Response(
            $this->templating->render(self::TEMPLATE_MAIN_ERRORS[$statusCode], ['status_text' => $statusText]),
            $statusCode
        );
    }
}
