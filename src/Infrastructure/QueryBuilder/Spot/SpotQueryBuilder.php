<?php

namespace Proximum\Vimeet\Infrastructure\QueryBuilder\Spot;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\SpotUnavailability;

class SpotQueryBuilder extends QueryBuilder
{
    /**
     * {@inheritdoc}
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);

        $this
            ->select('spot')
            ->from(Spot::class, 'spot');
    }

    /**
     * Get only visio spot
     *
     * @param bool $visio
     *
     * @return SpotQueryBuilder
     */
    public function visio($visio = false)
    {
        $this
            ->andWhere('spot.visio = :visio')
            ->setParameter('visio', $visio);

        return $this;
    }

    /**
     * Filter spot by Event
     *
     * @param Event $event
     *
     * @return SpotQueryBuilder
     */
    public function filterByEvent(Event $event)
    {
        $this->andWhere('spot.event = :event')
            ->setParameter('event', $event);

        return $this;
    }

    /**
     * Filter by active spot
     *
     * @return SpotQueryBuilder
     */
    public function active()
    {
        $this->andWhere('spot.active = true');

        return $this;
    }

    /**
     * Get meeting sheets assigned to spot in order to sort Spots list by assigned spots then by shared spots
     *
     * @param Sheet $fromSheet
     * @param Sheet $toSheet
     */
    public function meetingSheets(Sheet $fromSheet, Sheet $toSheet)
    {
        $this
            ->addSelect('sheetAssignedToSpot.id AS HIDDEN hasSheetAssignedFromMeeting')
            ->leftJoin('spot.sheets', 'sheetAssignedToSpot', 'WITH', 'sheetAssignedToSpot IN (:fromSheetId, :toSheetId)')
            // Exclude spots assigned to others sheet
            ->andWhere('sheetAssignedToSpot IN (:fromSheetId, :toSheetId) OR NOT EXISTS(SELECT sheet.id FROM Entity:Sheet sheet WHERE sheet.spot = spot AND sheet NOT IN (:fromSheetId, :toSheetId))')
            ->setParameter('fromSheetId', $fromSheet->getId())
            ->setParameter('toSheetId', $toSheet->getId())
            ->addOrderBy('hasSheetAssignedFromMeeting', 'DESC');
    }

    /**
     * Query spot that has not any spot unavailabily on the current slot
     *
     * @param MeetingSlot $slot
     *
     * @return SpotQueryBuilder
     *
     * @see SpotUnavailability
     */
    public function hasNotSpotUnavailability(MeetingSlot $slot)
    {
        $subquery = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('su.id')
            ->from(SpotUnavailability::class, 'su')
            ->where('su.slot = :slot')
            ->andWhere('su.spot = spot')
            ->setParameter('slot', $slot);

        $this->andWhere(
            $this->expr()->not(
                $this->expr()->exists($subquery->getDQL())
            )
        );

        return $this;
    }
}
