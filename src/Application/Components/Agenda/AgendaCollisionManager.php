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
        foreach ($unavailabilityViews as $unavailabilityView) {
            $massKey = $this->isOverlapping($unavailabilityView, $massViews);
            if (false !== $massKey) {
                unset($massViews[$massKey]);
            }
        }

        foreach ($massViews as $massView) {
            if ($massView->isBlocking) {
                $this->removeArrayElementIfNotNull(
                    $this->isOverlapping($massView, $massViews),
                    $massViews
                );
            }
        }

        foreach ($happeningViews as $happeningView) {
            $this->removeArrayElementIfNotNull(
                $this->isOverlapping($happeningView, $massViews),
                $massViews
            );

            $this->removeArrayElementIfNotNull(
                $this->isOverlapping($happeningView, $unavailabilityViews),
                $unavailabilityViews
            );
        }

        foreach ($meetingViews as $meetingView) {
            $massKey = $this->isOverlapping($meetingView, $massViews);

            if ($massKey !== false) {
                unset($massViews[$massKey]);
            }

            $unavailabilityKey = $this->isOverlapping($meetingView, $unavailabilityViews);

            if ($unavailabilityKey !== false) {
                unset($unavailabilityViews[$unavailabilityKey]);
            }
        }

        $this->meetingViews = $meetingViews;
        $this->happeningViews = $happeningViews;
        $this->unavailabilityViews = $unavailabilityViews;
        $this->massViews = $massViews;

        return [
            $meetingViews,
            $happeningViews,
            $unavailabilityViews,
            $massViews
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
     * @return int|bool
     */
    private function isOverlapping(
        AbstractTimeEntityView $abstractTimeEntityView,
        array $abstractTimeEntityViews
    ) {
        foreach ($abstractTimeEntityViews as $abstractTimeEntity) {
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
                return array_search($abstractTimeEntity, $abstractTimeEntityViews);
            }
        }

        return false;
    }

    /**
     * @param bool|int $arrayKey
     * @param AbstractTimeEntityView[] $array
     */
    private function removeArrayElementIfNotNull($arrayKey, array $array)
    {
        if ($arrayKey !== false) {
            unset($array[$arrayKey]);
        }
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
