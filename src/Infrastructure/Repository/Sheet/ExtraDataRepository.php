<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Sheet;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\ExtraData;
use Proximum\Vimeet\Domain\Repository\Sheet\ExtraDataRepositoryInterface;

class ExtraDataRepository implements ExtraDataRepositoryInterface
{
    /** @var EntityManager */
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
    public function add(ExtraData $extraData)
    {
        $this->entityManager->persist($extraData);
        $this->entityManager->flush($extraData);
    }

    /**
     * {@inheritdoc}
     */
    public function set(ExtraData $extraData)
    {
        $this->entityManager->flush($extraData);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraDataValuesForEvent(Event $event, string $name, array $values): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('extraData')
            ->from(ExtraData::class, 'extraData')
            ->join(
                'extraData.sheet',
                'sheet',
                'WITH',
                'sheet.event = :event AND extraData.name = :name AND extraData.value IN (:values)'
            )
            ->setParameter('event', $event)
            ->setParameter('name', $name)
            ->setParameter('values', $values)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraDataByEventAndName(Event $event, string $name): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('extraData')
            ->from(ExtraData::class, 'extraData')
            ->join(
                'extraData.sheet',
                'sheet',
                'WITH',
                'sheet.event = :event AND extraData.name = :name'
            )
            ->setParameter('event', $event)
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function hasExtraDataForSheet(Sheet $sheet, string $name): bool
    {
        return null !== $this->getExtraDataForSheet($sheet, $name);
    }

    public function getExtraDataForSheet(Sheet $sheet, string $name): ?ExtraData
    {
        return $this
            ->getExtraDataForSheetQueryBuilder($sheet, $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param Sheet  $sheet
     * @param string $name
     *
     * @return QueryBuilder
     */
    private function getExtraDataForSheetQueryBuilder(Sheet $sheet, string $name): QueryBuilder
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('extraData')
            ->from(ExtraData::class, 'extraData')
            ->where('extraData.sheet = :sheet AND extraData.name = :name')
            ->setParameter('sheet', $sheet)
            ->setParameter('name', $name)
        ;
    }
}
