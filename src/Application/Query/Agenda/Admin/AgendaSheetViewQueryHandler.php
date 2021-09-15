<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\View\Agenda\Admin\RequestView;
use Proximum\Vimeet\Application\View\Agenda\AgendaSheetIndicatorView;
use Proximum\Vimeet\Application\View\Agenda\AgendaSheetView;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Planner\IndicatorCalculator;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class AgendaSheetViewQueryHandler
{
    /**
     * @var AgendaParticipantViewQueryHandler
     */
    private $agendaParticipantViewQueryHandler;

    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeningParticipationRepository;

    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * @var MassRepositoryInterface
     */
    private $massUnavailabilityRepository;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepositoryInterface;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var RequestViewQueryHandler
     */
    private $requestViewQueryHandler;

    /**
     * @var MassAssignmentRepositoryInterface
     */
    private $massAssignmentRepository;

    /**
     * @var IndicatorCalculator
     */
    private $indicatorCalculator;

    /**
     * @param AgendaParticipantViewQueryHandler         $agendaParticipantViewQueryHandler
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param UnavailabilityRepositoryInterface         $unavailabilityRepository
     * @param MassRepositoryInterface                   $massUnavailabilityRepository
     * @param MeetingRepositoryInterface                $meetingRepositoryInterface
     * @param RequestRepositoryInterface                $requestRepository
     * @param RequestViewQueryHandler                   $requestViewQueryHandler
     * @param MassAssignmentRepositoryInterface         $massAssignmentRepository
     * @param IndicatorCalculator                       $indicatorCalculator
     */
    public function __construct(
        AgendaParticipantViewQueryHandler $agendaParticipantViewQueryHandler,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        MassRepositoryInterface $massUnavailabilityRepository,
        MeetingRepositoryInterface $meetingRepositoryInterface,
        RequestRepositoryInterface $requestRepository,
        RequestViewQueryHandler $requestViewQueryHandler,
        MassAssignmentRepositoryInterface $massAssignmentRepository,
        IndicatorCalculator $indicatorCalculator
    ) {
        $this->agendaParticipantViewQueryHandler = $agendaParticipantViewQueryHandler;
        $this->happeningParticipationRepository  = $happeningParticipationRepository;
        $this->unavailabilityRepository          = $unavailabilityRepository;
        $this->massUnavailabilityRepository      = $massUnavailabilityRepository;
        $this->meetingRepositoryInterface        = $meetingRepositoryInterface;
        $this->requestRepository                 = $requestRepository;
        $this->requestViewQueryHandler           = $requestViewQueryHandler;
        $this->massAssignmentRepository          = $massAssignmentRepository;
        $this->indicatorCalculator               = $indicatorCalculator;
    }

    /**
     * @param AgendaSheetViewQuery $query
     *
     * @return AgendaSheetView
     */
    public function handle(AgendaSheetViewQuery $query)
    {
        $meetings = $this->meetingRepositoryInterface->findBySheet($query->sheet);
        $happeningParticipations = $this->happeningParticipationRepository->findBySheet($query->sheet);
        $assignment = $this->massAssignmentRepository->findBySheet($query->sheet);
        $masses = $this->massUnavailabilityRepository->findBlockingByEvent($query->sheet->getEvent());

        $unavailabilites = $this->unavailabilityRepository->findBySheet($query->sheet);
        $indicatorView   = $this->indicatorCalculator->getIndicator($query->sheet);

        $agendaSheetIndicatorView = new AgendaSheetIndicatorView(
            $indicatorView->maxMeetingAvailable,
            \count($meetings)
        );

        $participants = [];
        $requests     = [];

        foreach ($query->sheet->getParticipants() as $participant) {
            $participants[] = $this->agendaParticipantViewQueryHandler->handle(
                new AgendaParticipantViewQuery(
                    $participant,
                    $query->sheet->getEvent(),
                    $query->sheet,
                    $query->locale,
                    $happeningParticipations,
                    $unavailabilites,
                    $masses,
                    $meetings,
                    $assignment
                )
            );
        }

        $unassignedRequests = $this
            ->requestRepository
            ->getUnassignedRequestsBySheetAndEvent(
                $query->sheet,
                Request::STATE_APPROVED
            );

        foreach ($unassignedRequests as $request) {
            $requests[] = $this->requestViewQueryHandler->handle(
                new RequestViewQuery(
                    $request,
                    $query->sheet,
                    $query->locale
                )
            );
        }

        usort($requests, function (RequestView $first, RequestView $second) {
            return strcmp($first->sheetMetTitle, $second->sheetMetTitle);
        });

        return new AgendaSheetView($participants, $requests, $agendaSheetIndicatorView);
    }
}
