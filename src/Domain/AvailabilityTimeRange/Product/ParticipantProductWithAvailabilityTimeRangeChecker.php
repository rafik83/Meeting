<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\AvailabilityTimeRange\Product;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\OverlappedTimeRangeMerger;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\OverlappedTimeRangeTruncater;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\TimeRangeNotAccessibleView;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\TimeRangeView;

/**
 * This class checks that the product selected for the participant
 * can be used, or if there is a conflict with a meeting or an happening
 */
class ParticipantProductWithAvailabilityTimeRangeChecker
{
    /** @var OverlappedTimeRangeMerger */
    private $overlappedTimeRangeMerger;

    /** @var OverlappedTimeRangeTruncater */
    private $overlappedTimeRangeTruncater;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    public function __construct(
        OverlappedTimeRangeMerger $overlappedTimeRangeMerger,
        OverlappedTimeRangeTruncater $overlappedTimeRangeTruncater,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->overlappedTimeRangeMerger = $overlappedTimeRangeMerger;
        $this->overlappedTimeRangeTruncater = $overlappedTimeRangeTruncater;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param Participant $participant
     * @param Product     $product
     *
     * @return bool
     */
    public function canSetProduct(Participant $participant, Product $product): bool
    {
        $participantProduct = $participant->getParticipantProduct();

        if (null === $participantProduct) {
            return true;
        }

        $currentAvailabilityTimeRanges = $participantProduct->getAvailabilityTimeRanges();

        // If the previous product does not have time range, therefore it is ok to set the new one
        if (empty($currentAvailabilityTimeRanges)) {
            return true;
        }

        $newAvailabilityTimeRanges = $product->getAvailabilityTimeRanges();

        $newAvailabilityTimeRangesById = [];
        $timeRangeViews = [];
        $timeRangeNotAccessibleViews = [];
        $removedAvailabilityTimeRanges = [];

        foreach ($newAvailabilityTimeRanges as $newAvailabilityTimeRange) {
            $newAvailabilityTimeRangesById[$newAvailabilityTimeRange->getId()] = $newAvailabilityTimeRange;

            $timeRangeViews[] = new TimeRangeView(
                $newAvailabilityTimeRange->getBegin(),
                $newAvailabilityTimeRange->getEnd()
            );
        }

        foreach ($currentAvailabilityTimeRanges as $currentAvailabilityTimeRange) {
            if (!isset($newAvailabilityTimeRangesById[$currentAvailabilityTimeRange->getId()])) {
                $removedAvailabilityTimeRanges[$currentAvailabilityTimeRange->getId()] = $currentAvailabilityTimeRange;

                $timeRangeNotAccessibleViews[] = new TimeRangeNotAccessibleView(
                    $currentAvailabilityTimeRange->getBegin(),
                    $currentAvailabilityTimeRange->getEnd()
                );
            }
        }

        // If the new product add more or the same time range, it is ok to set it
        if (!empty($timeRangeNotAccessibleViews)) {
            return true;
        }

        $timeRangeViews = $this->overlappedTimeRangeMerger->merge($timeRangeViews);

        foreach ($timeRangeNotAccessibleViews as $timeRangeNotAccessibleView) {
            $timeRangeNotAccessibleTruncated = $this->overlappedTimeRangeTruncater->truncate(
                $timeRangeNotAccessibleView,
                $timeRangeViews
            );

            if (empty($timeRangeNotAccessibleTruncated)) {
                continue;
            }

            foreach ($timeRangeNotAccessibleTruncated as $timeRange) {
                $participants = $this->participantRepository->getAvailableParticipants(
                    [$participant],
                    $timeRange->getBegin(),
                    $timeRange->getEnd(),
                    null,
                    null,
                    true
                );

                if (empty($participants)) {
                    return false;
                }
            }
        }

        return true;
    }
}
