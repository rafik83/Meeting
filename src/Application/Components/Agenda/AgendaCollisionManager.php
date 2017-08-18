<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
            $this->massViews = $this->removeElementIfOverlapping($unavailabilityView, $this->massViews);
        }

        foreach ($this->massViews as $massView) {
            if ($massView->isBlocking) {
                $this->massViews = $this->removeElementIfOverlapping($massView, $this->massViews);
            }
        }

        foreach ($this->happeningViews as $happeningView) {
            $this->massViews = $this->removeElementIfOverlapping($happeningView, $this->massViews);
            $this->unavailabilityViews = $this->removeElementIfOverlapping($happeningView, $this->unavailabilityViews);
        }

        foreach ($this->meetingViews as $meetingView) {
            $this->massViews = $this->removeElementIfOverlapping($meetingView, $this->massViews);
            $this->unavailabilityViews = $this->removeElementIfOverlapping($meetingView, $this->unavailabilityViews);
        }

        return [
            $this->meetingViews,
            $this->happeningViews,
            $this->unavailabilityViews,
            $this->massViews
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
     * @param AbstractTimeEntityView $abstractTimeEntityView
     * @param AbstractTimeEntityView[] $abstractTimeEntityViews
     *
     * @return AbstractTimeEntityView[]
     */
    private function removeElementIfOverlapping(
        AbstractTimeEntityView $abstractTimeEntityView,
        array &$abstractTimeEntityViews
    ): array {
        foreach ($abstractTimeEntityViews as $abstractTimeEntityKey => $abstractTimeEntity) {
            if ($this->doesFirstBeginAfterAndFinishBeforeSecond(
                    $abstractTimeEntityView,
                    $abstractTimeEntity
                ) || $this->doesFirstFinishAfterSecondBeginAndFinishBeforeSecondEnd(
                    $abstractTimeEntityView,
                    $abstractTimeEntity
                ) || $this->doesFirstBeginBeforeAndFinishAfterSecond(
                    $abstractTimeEntityView,
                    $abstractTimeEntity
                )
            ) {
                $this->removeArrayElement($abstractTimeEntityKey, $abstractTimeEntityViews);
            }
        }

        return $abstractTimeEntityViews;
    }

    /**
     * @param int $arrayKey
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
    private function doesFirstFinishAfterSecondBeginAndFinishBeforeSecondEnd (
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
    private function doesFirstBeginBeforeAndFinishAfterSecond (
        AbstractTimeEntityView $firstTimeEntity,
        AbstractTimeEntityView $secondTimeEntity
    ): bool {
        return $firstTimeEntity->getBegin() <= $secondTimeEntity->getBegin()
            && $firstTimeEntity->getEnd() >= $secondTimeEntity->getEnd();
    }
}
