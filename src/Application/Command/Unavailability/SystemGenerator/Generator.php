<?php

/*
 * This file is part of the Proximumn Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability\SystemGenerator;

use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Domain\Time\TimeOverlap;

class Generator
{
    /** @var UnavailabilityRepositoryInterface */
    private $unavailabilityRepository;

    /** @var AvailabilityTimeRangeRepositoryInterface */
    private $availabilityTimeRangeRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    public function __construct(
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        AvailabilityTimeRangeRepositoryInterface $availabilityTimeRangeRepository,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->availabilityTimeRangeRepository = $availabilityTimeRangeRepository;
        $this->participantRepository = $participantRepository;
    }

    public function generateSystemUnavailability(Event $event, User $user): void
    {
        $this->unavailabilityRepository->removeSystemUnavailabilityForUserAndEvent($user, $event);

        $availabilityTimeRanges = $this->availabilityTimeRangeRepository->findByEvent($event);
        /** @var AvailabilityTimeRange[] $availabilityTimeRangesBought */
        $availabilityTimeRangesBought = [];

        if (empty($availabilityTimeRanges)) {
            return;
        }

        /** @var Participant[] $participants */
        $participants = $this->participantRepository->getAllParticipantForUser($event, $user);
        $products = $this->getProductsForParticipants($participants);

        if (empty($products)) {
            return;
        }

        foreach ($products as $product) {
           foreach ($product->getAvailabilityTimeRanges() as $availabilityTimeRange) {
               $availabilityTimeRangesBought[$availabilityTimeRange->getId()] = $availabilityTimeRange;
           }
        }

        $timeRanges = [];
        foreach ($availabilityTimeRangesBought as $availabilityTimeRange) {
            $timeRange = new TimeRangeView($availabilityTimeRange->getBegin(), $availabilityTimeRange->getEnd());

            $timeRanges[] = $timeRange;
        }

        $timeRangesNotAccessible = [];
        foreach ($availabilityTimeRanges as $availabilityTimeRange) {
            $timeRangesNotAccessible[] = new TimeRangeNotAccessibleView($availabilityTimeRange->getBegin(), $availabilityTimeRange->getEnd());
        }

        $timeRangesNotAccessible = [];//$this->mergeOverlappedTimeRange($timeRangesNotAccessible);
    }

    /**
     * @param Participant[] $participants
     *
     * @return Product[]
     */
    private function getProductsForParticipants(array $participants): array
    {
        $products = [];

        foreach ($participants as $participant) {
            // If a participant has a package that is not passable, it will not be
            // able to buy a product that allows him to access availability time ranges
            // Therefore, full access
            if (!$participant->getSheet()->getPackage()->isPassable()) {
                return [];
            }

            $product = $participant->getParticipantProduct();

            if ($product instanceof Product) {
                $products[$product->getId()] = $product;
            }
        }

        return $products;
    }
}
