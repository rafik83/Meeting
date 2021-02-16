<?php

namespace Proximum\Vimeet\Infrastructure\QueryBuilder\Spot;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Spot;

class FilteredQueryBuilder extends QueryBuilder
{
    /**
     * {@inheritdoc}
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);

        $this
            ->select('spot, sheets')
            ->from(Spot::class, 'spot')
            ->leftJoin('spot.sheets', 'sheets')
            ->orderBy('spot.reference');
    }

    /**
     * @param Event $event
     *
     * @return FilteredQueryBuilder
     */
    public function hasEvent(Event $event)
    {
        $this
            ->where('spot.event = :event')
            ->setParameter('event', $event);

        return $this;
    }

    /**
     * @param array $filter
     *
     * @return FilteredQueryBuilder
     */
    public function filter(array $filter)
    {
        $this->filterByActive($filter);
        $this->filterByReference($filter);
        $this->filterByMeetingCapacity($filter);
        $this->filterBySeetCapacity($filter);
        $this->filterBySize($filter);

        return $this;
    }

    /**
     * @param array $filter
     */
    protected function filterByActive(array $filter)
    {
        if (isset($filter['active'])) {
            $this
                ->andWhere('spot.active = :active')
                ->setParameter('active', $filter['active'])
            ;
        }
    }

    /**
     * @param array $filter
     */
    protected function filterByReference(array $filter)
    {
        if (isset($filter['reference'])) {
            $this
                ->andWhere('spot.reference LIKE :reference')
                ->setParameter('reference', '%' . $filter['reference'] . '%')
            ;
        }
    }

    /**
     * @param array $filter
     */
    protected function filterByMeetingCapacity(array $filter)
    {
        if (isset($filter['meetingCapacity'])) {
            $this
                ->andWhere('spot.meetingCapacity = :meetingCapacity')
                ->setParameter('meetingCapacity', $filter['meetingCapacity'])
            ;
        }
    }

    /**
     * @param array $filter
     */
    protected function filterBySeetCapacity(array $filter)
    {
        if (isset($filter['seatCapacity'])) {
            $this
                ->andWhere('spot.seatCapacity = :seatCapacity')
                ->setParameter('seatCapacity', $filter['seatCapacity'])
            ;
        }
    }

    /**
     * @param array $filter
     */
    protected function filterBySize(array $filter)
    {
        if (isset($filter['size'])) {
            $this
                ->andWhere('spot.size = :size')
                ->setParameter('size', $filter['size'])
            ;
        }
    }
}
