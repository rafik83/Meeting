<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;

/**
 * Base class for listeners that are able to redirect to the event homepage.
 */
abstract class AbstractRedirectToEventListener
{
    /** @var RouterInterface */
    private $router;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var string */
    private $adminDomain;

    /**
     * @param RouterInterface          $router
     * @param EventRepositoryInterface $eventRepository
     * @param string                   $adminDomain
     */
    public function __construct(RouterInterface $router, EventRepositoryInterface $eventRepository, string $adminDomain)
    {
        $this->router          = $router;
        $this->eventRepository = $eventRepository;
        $this->adminDomain     = $adminDomain;
    }

    /**
     * Redirect to event fallback locale.
     *
     * @param GetResponseEvent $getResponseEvent
     */
    protected function handleRedirect(GetResponseEvent $getResponseEvent)
    {
        if (!$getResponseEvent->isMasterRequest()) {
            return;
        }

        $request = $getResponseEvent->getRequest();
        $host = $request->getHost();

        if ($this->adminDomain === $host) {
            return;
        }

        $route = $request->attributes->get('_route');

        if ($this->isIgnoredRoute($route)) {
            return;
        }

        $event = $this->eventRepository->getEventByDomain($host);

        if (null === $event) {
            return;
        }

        return $this->doRedirect(
            $getResponseEvent,
            $request,
            $event,
            $request->getLocale(),
            $route
        );
    }

    /**
     * Checks whether the redirection should be skipped for the incoming request.
     *
     * @param string $route
     *
     * @return bool
     */
    abstract protected function isIgnoredRoute($route);

    /**
     * Sets the redirect response for the given GetResponseEvent.
     *
     * @param GetResponseEvent $getResponseEvent
     * @param Request          $request
     * @param Event            $event
     * @param string|null      $locale           The current Request locale
     * @param string           $route            The current Request route
     */
    abstract protected function doRedirect(GetResponseEvent $getResponseEvent, Request $request, Event $event, $locale, $route);

    /**
     * @param Request     $request
     * @param Event       $event
     * @param string      $route
     * @param null|string $locale
     * @param array       $parameters route parameters
     *
     * @return RedirectResponse
     */
    protected function createRedirectResponse(Request $request, Event $event, $route, $locale = null, array $parameters = [])
    {
        $path = $this->router->generate(
            $route,
            array_merge(
                $request->attributes->get('_route_params', []),
                ['_locale' => $locale ?: $event->getFallback()],
                $parameters
            )
        );

        return new RedirectResponse($path);
    }
}
