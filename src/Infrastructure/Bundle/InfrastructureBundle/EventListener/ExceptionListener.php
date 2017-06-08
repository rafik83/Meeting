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
     * @param EventRepositoryInterface             $eventRepository
     * @param EngineInterface                      $templating
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        EngineInterface $templating,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->eventRepository = $eventRepository;
        $this->templating = $templating;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    /**
     * @param GetResponseForExceptionEvent $responseForExceptionEvent
     */
    public function onKernelException(GetResponseForExceptionEvent $responseForExceptionEvent)
    {
        $request = $responseForExceptionEvent->getRequest();
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

            if (null === $event) {
                return;
            }
        } catch (\Exception $exception) {
            return;
        }

        $statusCode = $this->resolveHttpStatusCode($exception);

        $response = new Response(
            $this->templating->render(
                'EventBundle:Exception:error.html.twig',
                [
                    'event'       => $event,
                    'status_code' => $statusCode,
                    'status_text' => $exception->getMessage(),
                ]
            ),
            $statusCode
        );

        $responseForExceptionEvent->setResponse($response);
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

        if (!in_array($statusCode, [403, 404, 500])) {
            $statusCode = 500;
        }

        return $statusCode;
    }
}
