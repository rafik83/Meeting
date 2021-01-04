<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\MeetingRequest;

use Proximum\Vimeet\Application\Query\Meeting\Message\DiscussionMeetingRequestViewQuery;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\CatalogAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    /**
     * @param Request        $request
     * @param EventDomain    $eventDomain
     * @param Sheet          $sheet
     * @param MeetingRequest $meetingRequest
     *
     * @return Response
     */
    public function displayDiscussionAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        MeetingRequest $meetingRequest
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->denyAccessUnlessGranted(CatalogAccessVoter::VIEW, $eventDomain->getEvent());

        $discussion = $this
            ->get('tactician.commandbus.query')
            ->handle(new DiscussionMeetingRequestViewQuery($meetingRequest, $request->getLocale()));

        return $this->render('EventBundle:MeetingRequest:discussionModal.html.twig', [
            'discussion' => $discussion,
        ]);
    }
}
