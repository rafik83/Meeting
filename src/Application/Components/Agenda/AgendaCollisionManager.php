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
            if (null !== $massKey) {
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

            if ($massKey !== null) {
                unset($massViews[$massKey]);
            }

            $unavailabilityKey = $this->isOverlapping($meetingView, $unavailabilityViews);

            if ($unavailabilityKey !== null) {
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
     * @return int
     */
    private function isOverlapping(
        AbstractTimeEntityView $abstractTimeEntityView,
        array $abstractTimeEntityViews
    ) {
        foreach ($abstractTimeEntityViews as $abstractTimeEntity) {
            if (($abstractTimeEntityView->getBegin() >= $abstractTimeEntity->getBegin()
                && $abstractTimeEntityView->getBegin() < $abstractTimeEntity->getEnd()
                || $abstractTimeEntityView->getEnd() > $abstractTimeEntity->getBegin())
                && ($abstractTimeEntityView->getEnd() <= $abstractTimeEntity->getEnd()
                || $abstractTimeEntityView->getBegin() <= $abstractTimeEntity->getBegin()
                && $abstractTimeEntityView->getEnd() >= $abstractTimeEntity->getEnd())
            ) {
                return array_search($abstractTimeEntity, $abstractTimeEntityViews);
            }
        }
    }

    /**
     * @param null|int $arrayKey
     * @param AbstractTimeEntityView[] $array
     */
    private function removeArrayElementIfNotNull(?int $arrayKey, array $array)
    {
        if ($arrayKey !== null) {
            unset($array[$arrayKey]);
        }
    }
}
