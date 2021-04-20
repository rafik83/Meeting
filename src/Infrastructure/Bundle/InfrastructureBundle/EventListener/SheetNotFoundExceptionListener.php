<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class SheetNotFoundExceptionListener extends AbstractRedirectToEventListener
{
    const TARGET_ROUTE = 'event';

    /**
     * Redirects to event route on {@link SheetNotFoundException}.
     *
     * @param GetResponseForExceptionEvent $getResponseForExceptionEvent
     *
     * @return RedirectResponse|null
     */
    public function onKernelException(ExceptionEvent $getResponseForExceptionEvent)
    {
        if (!$getResponseForExceptionEvent->getThrowable() instanceof SheetNotFoundException) {
            return null;
        }

        $this->handleRedirect($getResponseForExceptionEvent);
    }

    /**
     * {@inheritdoc}
     */
    protected function doRedirect(RequestEvent $getResponseEvent, Request $request, Event $event, $locale, $route)
    {
        $getResponseEvent->setResponse($this->createRedirectResponse($request, $event, self::TARGET_ROUTE, $locale));
    }

    /**
     * {@inheritdoc}
     */
    protected function isIgnoredRoute($route)
    {
        return false;
    }
}
