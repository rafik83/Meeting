<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Happening;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Repository\Happening\SpeakerRepositoryInterface;

class SpeakerRepository implements SpeakerRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Speaker $speaker)
    {
        $this->entityManager->persist($speaker);
        $this->entityManager->flush($speaker);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Speaker $speaker)
    {
        $this->entityManager->flush($speaker);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Speaker $speaker)
    {
        $this->entityManager->remove($speaker);
        $this->entityManager->flush($speaker);
    }

    /**
     * {@inheritdoc}
     */
    public function allByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('speaker')
            ->from(Speaker::class, 'speaker')
            ->andWhere('speaker.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }
}
