<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Event\EventListQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class PastController extends AbstractController
{
    private QueryBusInterface $queryBus;

    public function __construct(QueryBusInterface $queryBus)
    {
        $this->queryBus = $queryBus;
    }

    public function listAction(UserInterface $admin): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $events = $this->queryBus->handle(new EventListQuery($admin, EventListQuery::STATE_PAST));

        return $this->render('AdminBundle:Event:past.html.twig', [
            'events' => $events,
        ]);
    }
}
