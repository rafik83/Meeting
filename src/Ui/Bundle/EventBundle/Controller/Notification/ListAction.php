<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Notification;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Notification\NotificationViewQuery;
use Proximum\Vimeet\Application\View\Notification\NotificationListView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ListAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var Environment */
    private $twig;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        Environment $twig
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->twig = $twig;
    }

    public function __invoke(EventDomain $eventDomain, Sheet $sheet)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        /** @var NotificationListView $notificationListView */
        $notificationListView = $this->queryBus->handle(
            new NotificationViewQuery($sheet)
        );

        return new Response($this->twig->render('EventBundle:Notification:list.html.twig', [
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'notifications' => $notificationListView,
        ]));
    }
}
