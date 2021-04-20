<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\MeetingRequest;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Meeting\Message\DiscussionMeetingRequestViewQuery;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\CatalogAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends AbstractController
{
    private QueryBusInterface $queryBus;

    public function __construct(QueryBusInterface $queryBus) {
        $this->queryBus = $queryBus;
    }

    public function displayDiscussionAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        MeetingRequest $meetingRequest
    ):Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->denyAccessUnlessGranted(CatalogAccessVoter::VIEW, $eventDomain->getEvent());

        $discussion = $this->queryBus
            ->handle(new DiscussionMeetingRequestViewQuery($meetingRequest, $request->getLocale()));

        return $this->render('EventBundle:MeetingRequest:discussionModal.html.twig', [
            'discussion' => $discussion,
        ]);
    }
}
