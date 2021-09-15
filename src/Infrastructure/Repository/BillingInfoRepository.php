<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class BillingInfoRepository implements BillingInfoRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    private $cachedBillingInfoBySheetId;

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
    public function getBySheet(Sheet $sheet)
    {
        if (isset($this->cachedBillingInfoBySheetId[$sheet->getId()])) {
            return $this->cachedBillingInfoBySheetId[$sheet->getId()];
        }

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('billing_info')
            ->from(BillingInfo::class, 'billing_info')
            ->where('billing_info.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function loadBySheets(array $sheets): void
    {
        $billingInfos = $this->getBySheets($sheets);

        foreach ($billingInfos as $billingInfo) {
            $this->cachedBillingInfoBySheetId[$billingInfo->getSheet()->getId()] = $billingInfo;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBySheets(array $sheets)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('billing_info')
            ->from(BillingInfo::class, 'billing_info')
            ->where('billing_info.sheet IN (:sheets)')
            ->setParameter('sheets', $sheets);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(BillingInfo $billingInfo)
    {
        $this->entityManager->persist($billingInfo);
        $this->entityManager->flush($billingInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function set(BillingInfo $billingInfo)
    {
        $this->entityManager->flush($billingInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('billing_info, sheet')
            ->from(BillingInfo::class, 'billing_info')
            ->join('billing_info.sheet', 'sheet', 'WITH', 'sheet.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }
}
