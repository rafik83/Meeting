<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Messaging;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Proximum\Vimeet\Domain\Model\Sheet;

class CampaignRepository implements CampaignRepositoryInterface
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
    public function add(Campaign $campaign)
    {
        $this->entityManager->persist($campaign);
        $this->entityManager->flush($campaign);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Campaign $campaign)
    {
        $this->entityManager->flush($campaign);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->from(Campaign::class, 'campaign')
            ->select('campaign')
            ->where('campaign.event = :event')
            ->orderBy('campaign.createdAt', 'DESC')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->from(Campaign::class, 'campaign')
            ->select('campaign')
            ->where('campaign.id = :id')
            ->setParameter('id', $id)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getBySheet(Sheet $sheet): array
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('message.id')
            ->from(Campaign::class, 'campaign')
            ->join('campaign.message', 'message')
            ->join('campaign.sheets', 'sheet', 'WITH', 'sheet = :sheet')
            ->setParameter('sheet', $sheet);

        return $queryBuilder->getQuery()->getResult();
    }
}
