<?php

namespace Proximum\Vimeet\Infrastructure\QueryBuilder\Meeting;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingQueryBuilder extends QueryBuilder
{
    /**
     * {@inheritdoc}
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);

        $this
            ->select('meeting')
            ->from(Meeting::class, 'meeting');
    }

    /**
     * @param Sheet $sheet
     *
     * @return self
     */
    public function sendBy(Sheet $sheet)
    {
        $this
            ->andWhere('meeting.fromSheet = :sendBy')
            ->setParameter('sendBy', $sheet);

        return $this;
    }

    /**
     * @param Sheet $sheet
     *
     * @return self
     */
    public function receivedBy(Sheet $sheet)
    {
        $this
            ->andWhere('meeting.toSheet = :receivedBy')
            ->setParameter('receivedBy', $sheet);

        return $this;
    }

    /**
     * @return self
     */
    public function count()
    {
        $this
            ->select('COUNT(meeting.id)');

        return $this;
    }

    /**
     * @return int
     */
    public function getIntResult()
    {
        return (int) $this->getQuery()->getSingleScalarResult();
    }
}
