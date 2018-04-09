<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\AvailabilityTimeRange\Product;

use Proximum\Vimeet\Domain\Exception\Package\PackageNotPassableException;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\User;
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

        $user = $participant->getUser();
        $event = $participant->getSheet()->getEvent();

        try {
            $othersAvailabilityTimeRanges = $this->getOtherAvailabilityTimeRangeBought($event, $user, $participant);
        } catch (PackageNotPassableException $exception) {
            // If the user has a package not passable, it means he/she has full access on the event
            return true;
        }

        $newAvailabilityTimeRanges = $product->getAvailabilityTimeRanges();

        list ($timeRangeViews, $timeRangeNotAccessibleViews) = $this->getTimeRangeAccessibleAndNotAccessible(
            $newAvailabilityTimeRanges,
            $othersAvailabilityTimeRanges,
            $currentAvailabilityTimeRanges
        );

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

    /**
     * @param Event       $event
     * @param User        $user
     * @param Participant $participant
     *
     * @return AvailabilityTimeRange[]
     *
     * @throws PackageNotPassableException
     */
    private function getOtherAvailabilityTimeRangeBought(Event $event, User $user, Participant $participant): array
    {
        $availabilityTimeRangesBought = [];

        $participants = $this->participantRepository->getAllParticipantForUser($event, $user);

        if (\count($participants) === 1) {
            return $availabilityTimeRangesBought;
        }

        foreach ($participants as $participantToChecker) {
            // We do not handle the current participant
            if ($participant->getId() === $participantToChecker->getId()) {
                continue;
            }

            if (!$participant->getSheet()->getPackage()->isPassable()) {
                throw new PackageNotPassableException('Package not passable');
            }

            $product = $participant->getParticipantProduct();

            if ($product instanceof Product) {
                foreach ($product->getAvailabilityTimeRanges() as $availabilityTimeRange) {
                    $availabilityTimeRangesBought[$availabilityTimeRange->getId()] = $availabilityTimeRange;
                }
            }
        }

        return $availabilityTimeRangesBought;
    }

    /**
     * @param array $newAvailabilityTimeRanges
     * @param array $othersAvailabilityTimeRanges
     * @param array $currentAvailabilityTimeRanges
     *
     * @return array of [
     *    TimeRangeView[],
     *    TimeRangeNotAccessibleView[],
     * ]
     */
    private function getTimeRangeAccessibleAndNotAccessible(
        array $newAvailabilityTimeRanges,
        array $othersAvailabilityTimeRanges,
        array $currentAvailabilityTimeRanges
    ): array {
        $newAvailabilityTimeRangesById = [];
        $timeRangeViews = [];
        $timeRangeNotAccessibleViews = [];

        foreach ($newAvailabilityTimeRanges as $newAvailabilityTimeRange) {
            if (!isset($newAvailabilityTimeRangesById[$newAvailabilityTimeRange->getId()])) {
                $newAvailabilityTimeRangesById[$newAvailabilityTimeRange->getId()] = $newAvailabilityTimeRange;

                $timeRangeViews[] = new TimeRangeView(
                    $newAvailabilityTimeRange->getBegin(),
                    $newAvailabilityTimeRange->getEnd()
                );
            }
        }

        foreach ($othersAvailabilityTimeRanges as $othersAvailabilityTimeRange) {
            if (!isset($newAvailabilityTimeRangesById[$othersAvailabilityTimeRange->getId()])) {
                $newAvailabilityTimeRangesById[$othersAvailabilityTimeRange->getId()] = $othersAvailabilityTimeRange;

                $timeRangeViews[] = new TimeRangeView(
                    $othersAvailabilityTimeRange->getBegin(),
                    $othersAvailabilityTimeRange->getEnd()
                );
            }
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

        return [
            $timeRangeViews,
            $timeRangeNotAccessibleViews
        ];
    }
}
