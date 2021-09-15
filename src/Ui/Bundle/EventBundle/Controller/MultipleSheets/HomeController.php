<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\MultipleSheets;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Sheet\Multiple\MultipleViewQuery;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class HomeController extends AbstractController
{
    private QueryBusInterface $queryBus;

    public function __construct(
        QueryBusInterface $queryBus
    ) {
        $this->queryBus = $queryBus;
    }

    public function selectSheetAction(EventDomain $eventDomain, UserInterface $user = null): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $event = $eventDomain->getEvent();

        $sheetViews = $this->queryBus->handle(
            new MultipleViewQuery($user, $event)
        );

        if (count($sheetViews) <= 1) {
            return $this->redirectToRoute('event');
        }

        return $this->render('EventBundle:MultipleSheets:select-sheet.html.twig', [
            'event'=> $event,
            'sheetViews' => $sheetViews,
        ]);
    }
}
