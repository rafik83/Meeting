<?php

namespace Proximum\Vimeet\Application\Components\Agenda;

use Proximum\Vimeet\Application\View\Agenda\AbstractTimeEntityView;
use Proximum\Vimeet\Application\View\Agenda\HappeningView;
use Proximum\Vimeet\Application\View\Agenda\MassUnavailabilityView;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Application\View\Agenda\UnavailabilityView;

class AgendaCollisionManager
{
    /** @var MeetingView[] */
    private $meetingViews;

    /** @var HappeningView[] */
    private $happeningViews;

    /** @var UnavailabilityView[] */
    private $unavailabilityViews;

    /** @var MassUnavailabilityView[] */
    private $massViews;

    /**
     * @param MeetingView[]            $meetingViews
     * @param HappeningView[]          $happeningViews
     * @param UnavailabilityView[]     $unavailabilityViews
     * @param MassUnavailabilityView[] $massViews
     *
     * @return array
     */
    public function handleCollision(
        array $meetingViews = [],
        array $happeningViews = [],
        array $unavailabilityViews = [],
        array $massViews = []
    ): array {
        $this->meetingViews = $meetingViews;
        $this->happeningViews = $happeningViews;
        $this->unavailabilityViews = $unavailabilityViews;
        $this->massViews = $massViews;

        foreach ($this->unavailabilityViews as $unavailabilityView) {
            $this->removeElementIfOverlapping($unavailabilityView, $this->massViews);
        }

        foreach ($this->massViews as $key => $massView) {
            if ($massView->isBlocking) {
                $this->removeElementIfOverlapping($massView, $this->massViews);
            }
        }

        foreach ($this->happeningViews as $happeningView) {
            $this->removeElementIfOverlapping($happeningView, $this->massViews);
        }

        foreach ($this->meetingViews as $meetingView) {
            $this->removeElementIfOverlapping($meetingView, $this->massViews);
        }

        return [
            $this->meetingViews,
            $this->happeningViews,
            $this->unavailabilityViews,
            $this->massViews,
        ];
    }

    /**
     * @return HappeningView[]
     */
    public function getHappeningViews(): array
    {
        return $this->happeningViews;
    }

    /**
     * @return MeetingView[]
     */
    public function getMeetingViews(): array
    {
        return $this->meetingViews;
    }

    /**
     * @return UnavailabilityView[]
     */
    public function getUnavailabilityViews(): array
    {
        return $this->unavailabilityViews;
    }

    /**
     * @return MassUnavailabilityView[]
     */
    public function getMassViews(): array
    {
        return $this->massViews;
    }

    /**
     * @param AbstractTimeEntityView   $needle
     * @param AbstractTimeEntityView[] $haystack
     */
    private function removeElementIfOverlapping(
        AbstractTimeEntityView $needle,
        array &$haystack
    ) {
        foreach ($haystack as $abstractTimeEntityKey => $abstractTimeEntity) {
            if ($needle === $abstractTimeEntity) {
                continue;
            }

            if ($needle instanceof MassUnavailabilityView
                && $abstractTimeEntity instanceof MassUnavailabilityView
                && $needle->isBlocking
                && $abstractTimeEntity->isBlocking
            ) {
                continue;
            }

            if ($this->doesFirstBeginAfterAndFinishBeforeSecond(
                    $needle,
                    $abstractTimeEntity
                ) || $this->doesFirstFinishAfterSecondBeginAndFinishBeforeSecondEnd(
                    $needle,
                    $abstractTimeEntity
                ) || $this->doesFirstBeginBeforeAndFinishAfterSecond(
                    $needle,
                    $abstractTimeEntity
                )
            ) {
                $this->removeArrayElement($abstractTimeEntityKey, $haystack);
            }
        }
    }

    /**
     * @param int                      $arrayKey
     * @param AbstractTimeEntityView[] $array
     */
    private function removeArrayElement(int $arrayKey, array &$array)
    {
        unset($array[$arrayKey]);
    }

    /**
     * @param AbstractTimeEntityView $firstTimeEntity
     * @param AbstractTimeEntityView $secondTimeEntity
     *
     * @return bool
     */
    private function doesFirstBeginAfterAndFinishBeforeSecond(
        AbstractTimeEntityView $firstTimeEntity,
        AbstractTimeEntityView $secondTimeEntity
    ): bool {
        return $firstTimeEntity->getBegin() >= $secondTimeEntity->getBegin()
            && $firstTimeEntity->getBegin() < $secondTimeEntity->getEnd();
    }

    /**
     * @param AbstractTimeEntityView $firstTimeEntity
     * @param AbstractTimeEntityView $secondTimeEntity
     *
     * @return bool
     */
    private function doesFirstFinishAfterSecondBeginAndFinishBeforeSecondEnd(
        AbstractTimeEntityView $firstTimeEntity,
        AbstractTimeEntityView $secondTimeEntity
    ): bool {
        return $firstTimeEntity->getEnd() > $secondTimeEntity->getBegin()
            && $firstTimeEntity->getEnd() <= $secondTimeEntity->getEnd();
    }

    /**
     * @param AbstractTimeEntityView $firstTimeEntity
     * @param AbstractTimeEntityView $secondTimeEntity
     *
     * @return bool
     */
    private function doesFirstBeginBeforeAndFinishAfterSecond(
        AbstractTimeEntityView $firstTimeEntity,
        AbstractTimeEntityView $secondTimeEntity
    ): bool {
        return $firstTimeEntity->getBegin() <= $secondTimeEntity->getBegin()
            && $firstTimeEntity->getEnd() >= $secondTimeEntity->getEnd();
    }
}
