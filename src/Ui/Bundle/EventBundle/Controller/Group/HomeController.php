<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Group;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Sheet\Group\GroupViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet\GroupVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    private QueryBusInterface $queryBus;

    public function __construct(
        QueryBusInterface $queryBus
    ) {
        $this->queryBus = $queryBus;
    }

    public function indexAction(EventDomain $eventDomain, Group $sheetGroup): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(GroupVoter::MANAGE, $sheetGroup);

        $event = $eventDomain->getEvent();

        $groupView = $this->queryBus->handle(
            new GroupViewQuery($sheetGroup)
        );

        return $this->render('EventBundle:Sheet/Group:index.html.twig', [
            'event'     => $event,
            'groupView' => $groupView,
        ]);
    }
}
