<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\MeetingSlot\GetAvailableSlotsQuery;
use Proximum\Vimeet\Application\Query\MeetingSlot\GetAvailableSlotsQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Admin\MeetingUpdateSpotView;
use Proximum\Vimeet\Application\View\Agenda\Admin\ParticipantView;
use Proximum\Vimeet\Application\View\Agenda\Admin\SpotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\SlotView;
use Proximum\Vimeet\Domain\Event\Day\DayHelper;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class MeetingUpdateSpotViewQueryHandler
{
    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var TranslatorInterface */
    private $translator;

    private GetAvailableSlotsQueryHandler $getAvailableSlotsQueryHandler;

    /**
     * @param SpotRepositoryInterface $spotRepository
     * @param SheetInfoGuesser        $sheetInfoGuesser
     * @param TranslatorInterface     $translator
     */
    public function __construct(
        SpotRepositoryInterface $spotRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        TranslatorInterface $translator,
        GetAvailableSlotsQueryHandler $getAvailableSlotsQueryHandler
    ) {
        $this->spotRepository   = $spotRepository;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->translator       = $translator;
        $this->getAvailableSlotsQueryHandler = $getAvailableSlotsQueryHandler;
    }

    /**
     * @param MeetingUpdateSpotViewQuery $query
     *
     * @return MeetingUpdateSpotView
     */
    public function handle(MeetingUpdateSpotViewQuery $query)
    {
        $meeting = $query->meeting;

        $slotView = $this->getAvailableSlotsQueryHandler->handle(
            new GetAvailableSlotsQuery($query->meeting, $query->visio, $query->sheet, false)
        );

        $metParticipantsCount = count($meeting->getMetParticipants($query->sheet));

        $slotsIdBySpotId = [];
        foreach ($slotView->availableSlots as $slot) {
            $spots = $this->spotRepository->getSpotsForSlotAndParticipantsQuantity(
                $slot,
                $metParticipantsCount + 1,
                $meeting,
                $meeting->getFromSheet(),
                $meeting->getToSheet(),
                $query->visio
            );

            foreach ($spots as $spot) {
                $slotsIdBySpotId[$spot->getId()][] = $slot->getId();
            }
        }

        return new MeetingUpdateSpotView(
            $meeting->getId(),
            $meeting->getSpot()->getId(),
            $meeting->isBlockedSlot(),
            $meeting->isBlockedSpot(),
            array_map(
                function (Spot $spot) use ($meeting, $slotsIdBySpotId) {
                    $label = $this->getSpotLabel($spot, $meeting);

                    return new SpotView(
                        $spot->getId(),
                        $label,
                        $spot->getSeatCapacity(),
                        $slotsIdBySpotId[$spot->getId()]
                    );
                },
                $this->spotRepository->getActiveByEvent($query->sheet->getEvent())
            ),
            array_map(
                function (Participant $participant) {
                    return new ParticipantView(
                        $participant->getId(),
                        $participant->getFullname()
                    );
                },
                $query->sheet->getParticipantsArray()
            ),
            array_map(
                function (Participant $participant) {
                    return $participant->getId();
                },
                $meeting->getParticipants($query->sheet)
            ),
            array_map(
                function (MeetingSlot $meetingSlot) {
                    $timeZone = $meetingSlot->getEvent()->getTimeZone();

                    $day = DayHelper::getFormatter(null, $timeZone)->format($meetingSlot->getBegin());
                    $begin = DayHelper::getHourFormatter(null, $timeZone)->format($meetingSlot->getBegin());
                    $end = DayHelper::getHourFormatter(null, $timeZone)->format($meetingSlot->getEnd());

                    $slotLabel = $this->translator->trans(
                        'form.update_meeting.children.meetingSlot.label.begin.end',
                        [
                            '%day%' => $day,
                            '%begin%' => $begin,
                            '%end%' => $end,
                        ],
                        'forms'
                    );

                    return new SlotView($meetingSlot->getId(), $slotLabel);
                },
                $slotView->availableSlots
            )
            ,
            $slotView->currentSheetAvailableSlotIds,
            $meeting->getSlot()->getId(),
            $metParticipantsCount
        );
    }

    /**
     * @param Spot    $spot
     * @param Meeting $meeting
     *
     * @return null|string
     */
    private function getAssignedSheetTitle(Spot &$spot, Meeting &$meeting)
    {
        foreach ($meeting->getSheets() as $sheet) {
            if (null !== $sheet->getSpot() && $sheet->getSpot()->getId() === $spot->getId()) {
                return $this->sheetInfoGuesser->guessSheetTitle($sheet);
            }
        }

        return null;
    }

    /**
     * @param Spot    $spot
     * @param Meeting $meeting
     *
     * @return string
     */
    private function getSpotLabel(Spot $spot, Meeting $meeting)
    {
        $assignedSheetTitle = $this->getAssignedSheetTitle($spot, $meeting);

        if ($spot->isVisio()) {
            $visioLabel = $this->translator->trans('admin.agenda.meeting.updateSpot.visio');
            $label = null === $assignedSheetTitle
                ? $spot->getReference() . ' - ' . $visioLabel
                : sprintf(
                    '%s - %s - %s',
                    $spot->getReference(),
                    $visioLabel,
                    $assignedSheetTitle
                );
        } else {
            $label = null === $assignedSheetTitle
                ? $spot->getReference()
                : sprintf(
                    '%s - %s',
                    $spot->getReference(),
                    $assignedSheetTitle
                );
        }

        return $label;
    }
}
