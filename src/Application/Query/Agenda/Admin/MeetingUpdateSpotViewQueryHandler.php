<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Agenda\Admin\MeetingUpdateSpotView;
use Proximum\Vimeet\Application\View\Agenda\Admin\SpotView;
use Proximum\Vimeet\Domain\Model\Meeting;
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

    /**
     * @param SpotRepositoryInterface $spotRepository
     * @param SheetInfoGuesser        $sheetInfoGuesser
     * @param TranslatorInterface     $translator
     */
    public function __construct(
        SpotRepositoryInterface $spotRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        TranslatorInterface $translator
    ) {
        $this->spotRepository   = $spotRepository;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->translator       = $translator;
    }

    /**
     * @param MeetingUpdateSpotViewQuery $query
     *
     * @return MeetingUpdateSpotView
     */
    public function handle(MeetingUpdateSpotViewQuery $query)
    {
        $meeting = $query->meeting;

        return new MeetingUpdateSpotView(
            $meeting->getId(),
            $meeting->getSpot()->getId(),
            $meeting->isBlockedSlot(),
            $meeting->isBlockedSpot(),
            array_map(function (Spot $spot) use ($meeting) {
                $label = $this->getSpotLabel($spot, $meeting);

                return new SpotView(
                    $spot->getId(),
                    $label
                );
            }, $this->spotRepository->getSpotsForMeeting($meeting, $query->visio))
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
