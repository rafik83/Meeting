<?php

namespace Proximum\Vimeet\Domain\Unavailability\SystemGenerator;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Unavailability\System\SystemUnavailabilityForUserGeneratedEvent;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\CartRowParticipant;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CartRowParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Domain\Time\AbstractTimeRange;
use Proximum\Vimeet\Domain\Time\OverlappedTimeRangeMerger;
use Proximum\Vimeet\Domain\Time\OverlappedTimeRangeTruncater;
use Proximum\Vimeet\Domain\Time\TimeRangeNotAccessibleView;
use Proximum\Vimeet\Domain\Time\TimeRangeView;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Generator
{
    /** @var UnavailabilityRepositoryInterface */
    private $unavailabilityRepository;

    /** @var AvailabilityTimeRangeRepositoryInterface */
    private $availabilityTimeRangeRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var OverlappedTimeRangeMerger */
    private $overlappedTimeRangeMerger;

    /** @var OverlappedTimeRangeTruncater */
    private $overlappedTimeRangeTruncater;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var CartRowParticipantRepositoryInterface */
    private $cartRowParticipantRepository;

    public function __construct(
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        AvailabilityTimeRangeRepositoryInterface $availabilityTimeRangeRepository,
        ParticipantRepositoryInterface $participantRepository,
        OverlappedTimeRangeMerger $overlappedTimeRangeMerger,
        OverlappedTimeRangeTruncater $overlappedTimeRangeTruncater,
        EventDispatcherInterface $eventDispatcher,
        CartRowParticipantRepositoryInterface $cartRowParticipantRepository
    ) {
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->availabilityTimeRangeRepository = $availabilityTimeRangeRepository;
        $this->participantRepository = $participantRepository;
        $this->overlappedTimeRangeMerger = $overlappedTimeRangeMerger;
        $this->overlappedTimeRangeTruncater = $overlappedTimeRangeTruncater;
        $this->eventDispatcher = $eventDispatcher;
        $this->cartRowParticipantRepository = $cartRowParticipantRepository;
    }

    public function generateSystemUnavailability(Event $event, User $user): void
    {
        $this->unavailabilityRepository->removeSystemUnavailabilityForUserAndEvent($user, $event);

        $availabilityTimeRanges = $this->availabilityTimeRangeRepository->findByEvent($event);

        if (empty($availabilityTimeRanges)) {
            $this->dispatchSystemUnavailabilityGeneratedEvent($event, $user);

            return;
        }

        /** @var Participant[] $participants */
        $participants = $this->participantRepository->getAllParticipantForUser($event, $user);

        if (empty($participants)) {
            $this->dispatchSystemUnavailabilityGeneratedEvent($event, $user);

            return;
        }

        $products = $this->getProductsForParticipants($participants);

        if (empty($products)) {
            $this->dispatchSystemUnavailabilityGeneratedEvent($event, $user);

            return;
        }

        $availabilityTimeRangesBought = $this->getAvailabilityTimeRangeOutOfProducts($products);

        $timeRanges = $this->getTimeRanges($availabilityTimeRangesBought);
        $timeRangesNotAccessible = $this->getTimeRangesNotAccessible($availabilityTimeRanges, $availabilityTimeRangesBought);

        if (empty($timeRangesNotAccessible)) {
            $this->dispatchSystemUnavailabilityGeneratedEvent($event, $user);

            return;
        }

        $this->sortTimeRange($timeRanges);

        // We merge the time range that overlap with each other
        // And then cut them off the time range where the user is available to determine
        // the period of time where the user has not right to access
        $timeRangesNotAccessible = $this->overlappedTimeRangeMerger->merge($timeRangesNotAccessible);
        $timeRangeOfUnavailability = $this->getUnavailabilitiesOutOfTimeRangesNotAccessible($timeRangesNotAccessible, $timeRanges);

        foreach ($timeRangeOfUnavailability as $unavailability) {
            $systemUnavailability = new Unavailability(
                $user,
                $event,
                $unavailability->getBegin(),
                $unavailability->getEnd(),
                null,
                Unavailability::CREATED_BY_SYSTEM
            );

            $this->unavailabilityRepository->add($systemUnavailability);
        }

        $this->dispatchSystemUnavailabilityGeneratedEvent($event, $user);
    }

    private function dispatchSystemUnavailabilityGeneratedEvent(Event $event, User $user): void
    {
        $systemUnavailabilityGeneratedEvent = new SystemUnavailabilityForUserGeneratedEvent($user, $event);
        $this->eventDispatcher->dispatch(Events::USER_SYSTEM_UNAVAILABILITY_GENERATED, $systemUnavailabilityGeneratedEvent);
    }

    private function sortTimeRange(array &$timeRanges): void
    {
        usort($timeRanges, function (AbstractTimeRange $timeRangeView, AbstractTimeRange $anotherTimeRangeView) {
            return $timeRangeView->getBegin() > $anotherTimeRangeView->getBegin();
        });
    }

    /**
     * @param Participant[] $participants
     *
     * @return Product[]
     */
    private function getProductsForParticipants(array &$participants): array
    {
        $products = [];

        foreach ($participants as $participant) {
            $product = $participant->getParticipantProduct();

            if ($product instanceof Product) {
                $products[$product->getId()] = $product;

                continue;
            }

            $product = $this->getProductOutOfCartRowParticipantForParticipant($participant);

            if ($product instanceof Product) {
                $products[$product->getId()] = $product;
            }
        }

        return $products;
    }

    private function getProductOutOfCartRowParticipantForParticipant(Participant $participant): ?Product
    {
        $cartRowParticipant = $this->cartRowParticipantRepository->findByParticipant($participant);

        if ($cartRowParticipant instanceof CartRowParticipant) {
            return $cartRowParticipant->getCartRow()->getProduct();
        }

        return null;
    }

    /**
     * @param AvailabilityTimeRange[] $availabilityTimeRanges
     * @param AvailabilityTimeRange[] $availabilityTimeRangesBought
     *
     * @return TimeRangeNotAccessibleView[]
     */
    private function getTimeRangesNotAccessible(array &$availabilityTimeRanges, array &$availabilityTimeRangesBought): array
    {
        $timeRangesNotAccessible = [];

        foreach ($availabilityTimeRanges as $availabilityTimeRange) {
            if (!isset($availabilityTimeRangesBought[$availabilityTimeRange->getId()])) {
                $timeRangesNotAccessible[] = new TimeRangeNotAccessibleView(
                    $availabilityTimeRange->getBegin(),
                    $availabilityTimeRange->getEnd()
                );
            }
        }

        return $timeRangesNotAccessible;
    }

    /**
     * @param AvailabilityTimeRange[] $availabilityTimeRangesBought
     *
     * @return TimeRangeView[]
     */
    private function getTimeRanges(array &$availabilityTimeRangesBought): array
    {
        $timeRanges = [];

        foreach ($availabilityTimeRangesBought as $availabilityTimeRange) {
            $timeRange = new TimeRangeView($availabilityTimeRange->getBegin(), $availabilityTimeRange->getEnd());

            $timeRanges[] = $timeRange;
        }

        return $timeRanges;
    }

    /**
     * @param Product[] $products
     *
     * @return AvailabilityTimeRange[]
     */
    private function getAvailabilityTimeRangeOutOfProducts(array $products): array
    {
        $availabilityTimeRangesBought = [];
        foreach ($products as $product) {
            foreach ($product->getAvailabilityTimeRanges() as $availabilityTimeRange) {
                $availabilityTimeRangesBought[$availabilityTimeRange->getId()] = $availabilityTimeRange;
            }
        }

        return $availabilityTimeRangesBought;
    }

    /**
     * @param TimeRangeNotAccessibleView[] $timeRangesNotAccessible
     * @param TimeRangeView[]              $timeRanges
     *
     * @return TimeRangeNotAccessibleView[]
     */
    private function getUnavailabilitiesOutOfTimeRangesNotAccessible(array &$timeRangesNotAccessible, array &$timeRanges): array
    {
        $timeRangesUnavailability = [];

        foreach ($timeRangesNotAccessible as $timeRangeNotAccessible) {
            $truncatedTimeRanges = $this->overlappedTimeRangeTruncater->truncate($timeRangeNotAccessible, $timeRanges);

            foreach ($truncatedTimeRanges as $truncatedTimeRange) {
                $timeRangesUnavailability[] = $truncatedTimeRange;
            }
        }

        return $timeRangesUnavailability;
    }
}
