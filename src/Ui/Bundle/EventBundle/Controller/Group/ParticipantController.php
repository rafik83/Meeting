<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Group;

use Proximum\Vimeet\Application\Query\Group\Participant\GroupViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet\GroupVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ParticipantController extends Controller
{
    public function listAction(EventDomain $eventDomain, Group $sheetGroup)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(GroupVoter::MANAGE, $sheetGroup);

        $event = $eventDomain->getEvent();

        $groupView = $this->get('tactician.commandbus.query')->handle(
            new GroupViewQuery($sheetGroup, $event)
        );

        return $this->render('EventBundle:Sheet/Group/Participant:list.html.twig', [
            'event'     => $event,
            'groupView' => $groupView,
        ]);
    }
}
