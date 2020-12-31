<?php

namespace Proximum\Vimeet\Infrastructure\QueryBuilder\Meeting\Request;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;

class RequestQueryBuilder extends QueryBuilder
{
    /**
     * {@inheritdoc}
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);

        $this
            ->select('request')
            ->from(Request::class, 'request');
    }

    /**
     * @param Sheet $sheet
     *
     * @return RequestQueryBuilder
     */
    public function sendBy(Sheet $sheet)
    {
        $this
            ->andWhere('request.from = :sendBy')
            ->setParameter('sendBy', $sheet);

        return $this;
    }

    /**
     * @param Sheet $sheet
     *
     * @return RequestQueryBuilder
     */
    public function receivedBy(Sheet $sheet)
    {
        $this
            ->andWhere('request.to = :receivedBy')
            ->setParameter('receivedBy', $sheet);

        return $this;
    }

    /**
     * @return RequestQueryBuilder
     */
    public function approved()
    {
        $this
            ->andWhere('request.state = :approved')
            ->setParameter('approved', Request::STATE_APPROVED);

        return $this;
    }

    /**
     * @return RequestQueryBuilder
     */
    public function pending()
    {
        $this
            ->andWhere('request.state = :pending')
            ->setParameter('pending', Request::STATE_SENT);

        return $this;
    }

    /**
     * @return RequestQueryBuilder
     */
    public function refused()
    {
        $this
            ->andWhere('request.state = :refused')
            ->setParameter('refused', Request::STATE_REFUSED);

        return $this;
    }

    /**
     * @return RequestQueryBuilder
     */
    public function count()
    {
        $this
            ->select('COUNT(request.id)');

        return $this;
    }

    /**
     * @return int
     */
    public function getIntResult()
    {
        return (int) $this->getQuery()->getSingleScalarResult();
    }

    /**
     * @return RequestQueryBuilder
     */
    public function mostRecentFirst()
    {
        $this
            ->orderBy('request.createdAt', 'DESC');

        return $this;
    }

    /**
     * @return RequestQueryBuilder
     */
    public function isEnabled()
    {
        $this->andWhere('request.disabled = false');

        return $this;
    }

    /**
     * @return RequestQueryBuilder
     */
    public function isToAttending()
    {
        $this->join('request.to', 'to', 'WITH', 'to.attend = true');

        return $this;
    }

    /**
     * @return RequestQueryBuilder
     */
    public function isFromAttending()
    {
        $this->join('request.from', 'sheetFrom', 'WITH', 'sheetFrom.attend = true');

        return $this;
    }

    /**
     * @param Event $event
     *
     * @return RequestQueryBuilder
     */
    public function fromEvent(Event $event)
    {
        $this->where('request.event = :event')->setParameter('event', $event);

        return $this;
    }
}
