<?php

namespace Proximum\Vimeet\Domain\AvailabilityTimeRange\Product;

use Proximum\Vimeet\Domain\Exception\Package\PackageNotPassableException;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Time\OverlappedTimeRangeMerger;
use Proximum\Vimeet\Domain\Time\OverlappedTimeRangeTruncater;
use Proximum\Vimeet\Domain\Time\TimeRangeNotAccessibleView;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

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

        if ($participantProduct->getId() === $product->getId()) {
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

        $timeRangeViews = $this->getTimeRangeViewsOutOfNewAvailabilityTimeRanges($newAvailabilityTimeRanges);
        $timeRangeViews = $this->getTimeRangeViewsOutOfOtherAvailabilityTimeRanges($othersAvailabilityTimeRanges, $timeRangeViews);
        $timeRangeNotAccessibleViews = $this->getTimeRangeNotAccessibleViews(
            $currentAvailabilityTimeRanges,
            $timeRangeViews
        );

        // If the new product add more or the same time range, it is ok to set the product checked
        if (empty($timeRangeNotAccessibleViews)) {
            return true;
        }

        $timeRangeViews = $this->overlappedTimeRangeMerger->merge($timeRangeViews);
        $timeRangeNotAccessibleViews = $this->overlappedTimeRangeMerger->merge($timeRangeNotAccessibleViews);

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
     * @throws PackageNotPassableException
     *
     * @return AvailabilityTimeRange[]
     */
    private function getOtherAvailabilityTimeRangeBought(Event $event, User $user, Participant $participant): array
    {
        $availabilityTimeRangesBought = [];

        $participants = $this->participantRepository->getAllParticipantForUser($event, $user);

        if (1 === \count($participants)) {
            return $availabilityTimeRangesBought;
        }

        foreach ($participants as $participantToChecker) {
            // We do not handle the current participant
            if ($participant->getId() === $participantToChecker->getId()) {
                continue;
            }

            if (!$participantToChecker->getSheet()->getPackage()->isPassable()) {
                throw new PackageNotPassableException('Package not passable');
            }

            $product = $participantToChecker->getParticipantProduct();

            if ($product instanceof Product) {
                foreach ($product->getAvailabilityTimeRanges() as $availabilityTimeRange) {
                    $availabilityTimeRangesBought[$availabilityTimeRange->getId()] = $availabilityTimeRange;
                }
            }
        }

        return $availabilityTimeRangesBought;
    }

    /**
     * @param AvailabilityTimeRange[] $newAvailabilityTimeRanges
     *
     * @return TimeRangeView[]
     */
    private function getTimeRangeViewsOutOfNewAvailabilityTimeRanges(
        array &$newAvailabilityTimeRanges
    ): array {
        $timeRangeViews = [];

        foreach ($newAvailabilityTimeRanges as $newAvailabilityTimeRange) {
            if (!isset($timeRangeViews[$newAvailabilityTimeRange->getId()])) {
                $timeRangeViews[$newAvailabilityTimeRange->getId()] = new TimeRangeView(
                    $newAvailabilityTimeRange->getBegin(),
                    $newAvailabilityTimeRange->getEnd()
                );
            }
        }

        return $timeRangeViews;
    }

    /**
     * @param AvailabilityTimeRange[] $othersAvailabilityTimeRanges
     * @param TimeRangeView[]         $timeRangeViews
     *
     * @return TimeRangeView[]
     */
    private function getTimeRangeViewsOutOfOtherAvailabilityTimeRanges(
        array &$othersAvailabilityTimeRanges,
        array $timeRangeViews
    ): array {
        foreach ($othersAvailabilityTimeRanges as $othersAvailabilityTimeRange) {
            if (!isset($timeRangeViews[$othersAvailabilityTimeRange->getId()])) {
                $timeRangeViews[$othersAvailabilityTimeRange->getId()] = new TimeRangeView(
                    $othersAvailabilityTimeRange->getBegin(),
                    $othersAvailabilityTimeRange->getEnd()
                );
            }
        }

        return $timeRangeViews;
    }

    /**
     * @param AvailabilityTimeRange[] $currentAvailabilityTimeRanges
     * @param TimeRangeView[]         $timeRangeViews
     *
     * @return TimeRangeNotAccessibleView[]
     */
    private function getTimeRangeNotAccessibleViews(
        array &$currentAvailabilityTimeRanges,
        array &$timeRangeViews
    ): array {
        $timeRangeNotAccessibleViews = [];

        foreach ($currentAvailabilityTimeRanges as $currentAvailabilityTimeRange) {
            if (!isset($timeRangeViews[$currentAvailabilityTimeRange->getId()])) {
                $removedAvailabilityTimeRanges[$currentAvailabilityTimeRange->getId()] = $currentAvailabilityTimeRange;

                $timeRangeNotAccessibleViews[] = new TimeRangeNotAccessibleView(
                    $currentAvailabilityTimeRange->getBegin(),
                    $currentAvailabilityTimeRange->getEnd()
                );
            }
        }

        return $timeRangeNotAccessibleViews;
    }
}
