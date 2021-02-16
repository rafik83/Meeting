<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\LockMeetingRequestUpdate;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\View\Meeting\AdminShowDetailsView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\FilterSummary;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\MeetingRequest\FilterMeetingRequestType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\MeetingRequest\LockMeetingRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MeetingRequestController extends AbstractController
{
    private FormFactoryInterface $formFactory;
    private FilterSummary $filterSummary;
    private RequestRepositoryInterface $meetingRequestRepository;
    private MessageRepositoryInterface $meetingMessageRepository;
    private MeetingSlotRepositoryInterface $meetingSlotRepository;
    private SheetInfoGuesser $sheetInfoGuesser;

    public function __construct(
        FormFactoryInterface $formFactory,
        FilterSummary $filterSummary,
        RequestRepositoryInterface $meetingRequestRepository,
        MessageRepositoryInterface $meetingMessageRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        $this->formFactory = $formFactory;
        $this->filterSummary = $filterSummary;
        $this->meetingRequestRepository = $meetingRequestRepository;
        $this->meetingMessageRepository = $meetingMessageRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    private function createFilterForm(string $type, array $data, array $options = []): FormInterface
    {
        return $this->formFactory->createNamed('', $type, $data, $options);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $filters    = [];
        $filtered   = false;
        $filterForm = $this->createFilterForm(FilterMeetingRequestType::class, [
            'state'   => $request->query->get('state'),
            'orderBy' => RequestRepositoryInterface::ORDER_BY_STATE_UPDATED_AT_DESC,
        ]);

        // The form is not valid because of the parameters page sent
        if ($filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid()) {
            $filters  = $filterForm->getData();
            $filtered = true;
        }

        $meetingRequests = $this
            ->meetingRequestRepository
            ->findByEventAndFilterByState($event, $request->query->getInt('page', 1), 20, $locale, $filters);

        $filterFormView = $filterForm->createView();

        return $this->render('AdminBundle:MeetingRequest:list.html.twig', [
            'event'            => $event,
            'meeting_requests' => $meetingRequests,
            'filter_form'      => $filterFormView,
            'filters_summary'  => $this->filterSummary->getFilters($filterFormView, $filters, $event, $locale),
            'filtered'         => $filtered,
        ]);
    }

    /**
     * @param Request        $request
     * @param Event          $event
     * @param MeetingRequest $meetingRequest
     *
     * @return Response
     */
    public function showDetailAction(Request $request, Event $event, MeetingRequest $meetingRequest)
    {
        $locale = $event->getAvailableLocale($request->getLocale());

        $messages = $this->meetingMessageRepository->getMessagesByMeetingRequest($meetingRequest);

        $meetingRequestView = new AdminShowDetailsView(
            $meetingRequest->getId(),
            $meetingRequest->getFromSheet()->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($meetingRequest->getFromSheet(), $locale),
            $meetingRequest->getToSheet()->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($meetingRequest->getToSheet(), $locale),
            array_map(
                function (Participant $participant) use ($locale) {
                    return $this->get('template.participant_info_guesser')
                        ->guessParticipantCompleteName($participant, $locale);
                },
                $meetingRequest->getFromParticipants()->toArray()
            ),
            array_map(
                function (Participant $participant) use ($locale) {
                    return $this->get('template.participant_info_guesser')
                        ->guessParticipantCompleteName($participant, $locale);
                },
                $meetingRequest->getToParticipants()->toArray()
            ),
            $messages,
            $meetingRequest->getState(),
            $meetingRequest->getCreatedAt(),
            $meetingRequest->getStateUpdatedAt()
        );

        return $this->render('AdminBundle:MeetingRequest:details.html.twig', [
            'event'              => $event,
            'meetingRequestView' => $meetingRequestView,
        ]);
    }

    /**
     * @deprecated
     */
    public function slotsAction(Request $request): JsonResponse
    {
        trigger_deprecation('vimeet', '1.80.0', 'This action has been marked as deprecated because it was calling MeetingSlotRepositoryInterface::findAvailableSlotIdByParticipantsIds that is not defined');
        return new JsonResponse([]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function lockMeetingRequestAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if (!$this->isGranted('ROLE_ALLOWED_TO_ORGANIZE')) {
            throw $this->createNotFoundException('Action not allowed for this user');
        }

        $lockMeetingRequest = new LockMeetingRequestUpdate(
            $event,
            $event->getConfiguration()->isMeetingRequestUpdateLocked()
        );
        $form = $this->createForm(LockMeetingRequestType::class, $lockMeetingRequest, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($lockMeetingRequest);
            $this->addFlash('success', 'flash.admin.meeting_request.lock.update.success');

            return $this->redirectToRoute('admin_meeting_request_lock_update', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:MeetingRequest:lock.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
