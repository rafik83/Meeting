<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardViewQuery;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class DashboardAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var DashboardViewQueryHandler */
    private $dashboardViewQueryHandler;

    /** @var Environment */
    private $twig;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        DashboardViewQueryHandler $dashboardViewQueryHandler,
        Environment $twig
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->dashboardViewQueryHandler = $dashboardViewQueryHandler;
        $this->twig = $twig;
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException();
        }

        $view = $this->dashboardViewQueryHandler->handle(
            new DashboardViewQuery($event, $event->getAvailableLocale($request->getLocale()))
        );

        return new Response($this->twig->render('AdminBundle:Event/Dashboard:index.html.twig', [
            'event'     => $event,
            'dashboard' => $view,
        ]));
    }
}
