<?php

namespace Proximum\Vimeet\Infrastructure\QueryBuilder\Meeting\Request;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class FilterQueryBuilder extends QueryBuilder
{
    /**
     * {@inheritdoc}
     */
    public function __construct(EntityManagerInterface $em, Event $event)
    {
        parent::__construct($em);

        $this
            ->select('request', 'fromSheet', 'toSheet')
            ->from(Request::class, 'request', 'request.id')
            ->join('request.from', 'fromSheet', 'WITH', 'request.disabled = false AND request.event = :event AND fromSheet.enable = true')
            ->join('request.to', 'toSheet', 'WITH', 'toSheet.enable = true')
            ->orderBy('request.stateUpdatedAt', 'DESC')
            ->setParameter('event', $event);
    }

    /**
     * Filter request which have meeting
     */
    public function filterPlanned()
    {
        $this->andWhere('EXISTS(SELECT m.id FROM Entity:Meeting m where m.request = request)');
    }

    /**
     * @param string $state
     */
    public function filterByState(string $state)
    {
        $this
            ->andWhere('request.state = :state')
            ->andWhere('NOT EXISTS(SELECT m.id FROM Entity:Meeting m where m.request = request)')
            ->setParameter('state', $state);
    }

    /**
     * @param string $order
     */
    public function order(string $order)
    {
        if (RequestRepositoryInterface::ORDER_BY_CREATE_AT_ASC === $order) {
            $this->orderBy('request.createdAt', 'ASC');
        } elseif (RequestRepositoryInterface::ORDER_BY_CREATE_AT_DESC === $order) {
            $this->orderBy('request.createdAt', 'DESC');
        } elseif (RequestRepositoryInterface::ORDER_BY_STATE_UPDATED_AT_ASC === $order) {
            $this->orderBy('request.stateUpdatedAt', 'ASC');
        } elseif (RequestRepositoryInterface::ORDER_BY_STATE_UPDATED_AT_DESC === $order) {
            $this->orderBy('request.stateUpdatedAt', 'DESC');
        }
    }
}
