<?php
/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
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

    /**
     * Route used on admin or in case of 500 http code on event
     */
    const ROUTES_ERRORS_TWIG = [
        Response::HTTP_INTERNAL_SERVER_ERROR => 'TwigBundle:Exception:error500.html.twig',
        Response::HTTP_NOT_FOUND             => 'TwigBundle:Exception:error404.html.twig',
        Response::HTTP_FORBIDDEN             => 'TwigBundle:Exception:error403.html.twig',
    ];

    /**
     * Route used on http error code on event
     */
    const ROUTES_ERRORS_EVENT = [
        Response::HTTP_NOT_FOUND => 'EventBundle:Exception:error404.html.twig',
        Response::HTTP_FORBIDDEN => 'EventBundle:Exception:error403.html.twig',
    ];

    /**
     * @param EventRepositoryInterface             $eventRepository
     * @param EngineInterface                      $templating
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        EngineInterface $templating,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->eventRepository             = $eventRepository;
        $this->templating                  = $templating;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    /**
     * @param GetResponseForExceptionEvent $responseForExceptionEvent
     */
    public function onKernelException(GetResponseForExceptionEvent $responseForExceptionEvent)
    {
        $request   = $responseForExceptionEvent->getRequest();
        $exception = $responseForExceptionEvent->getException();

        /**
         * Symfony throw a redirect when User is not logged
         * and there is AuthenticationException or AccessDeniedException
         * See Symfony\Component\Security\Http\Firewall\ExceptionListener
         */
        if (($exception instanceof AuthenticationException || $exception instanceof AccessDeniedException)
            && !$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
        ) {
            return;
        }

        /**
         * Try to get the event matching the host.
         */
        try {
            $event = $this->eventRepository->getEventByDomain($request->getHost());
        } catch (\Exception $exception) {
            return;
        }
        $statusCode = $this->resolveHttpStatusCode($exception);
        $responseForExceptionEvent->setResponse($this->buildResponseFromHttpStatusCode($statusCode, $event));
        $responseForExceptionEvent->stopPropagation();
    }

    /**
     * @param \Exception $exception
     *
     * @return int one of 403, 404, 500
     */
    private function resolveHttpStatusCode(\Exception $exception)
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

    /**
     * @param int        $statusCode
     * @param null|Event $event
     *
     * @return Response
     */
    private function buildResponseFromHttpStatusCode(int $statusCode, Event $event = null)
    {
        $route = self::ROUTES_ERRORS_TWIG[$statusCode];

        if (null !== $event && $statusCode !== Response::HTTP_INTERNAL_SERVER_ERROR) {
            $route = self::ROUTES_ERRORS_EVENT[$statusCode];
        }

        return new Response(
            $this->templating->render(
                $route,
                [
                    'event'   => $event,
                    'content' => sprintf('error.%s.content', $statusCode),
                ]
            ),
            $statusCode
        );
    }
}
