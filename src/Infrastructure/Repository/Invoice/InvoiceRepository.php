<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Invoice;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Invoice\Numero\InvoiceNumeroView;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * InvoiceRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilteredByEvents(array $events, \DateTimeInterface $beginDate, \DateTimeInterface $endDate)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('invoice', 'sheet')
            ->from(Invoice::class, 'invoice')
            ->join('invoice.sheet', 'sheet')
            ->where('invoice.event IN (:events)')
            ->andWhere('invoice.createdAt BETWEEN :beginDate and :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('events', $events)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByNumero(InvoiceNumeroView $invoiceNumeroView)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
            ->join('invoice.sheet', 'sheet', 'WITH', 'invoice.invoicePrefix = :prefix AND invoice.invoiceYear = :year AND invoice.invoiceIncrement = :increment')
            ->setParameter('prefix', $invoiceNumeroView->prefix)
            ->setParameter('year', $invoiceNumeroView->year)
            ->setParameter('increment', $invoiceNumeroView->increment)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(Invoice $invoice)
    {
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
    }

    public function set(Invoice $invoice): void
    {
        $this->entityManager->flush($invoice);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('invoice, orders')
            ->from(Invoice::class, 'invoice')
            ->join('invoice.orders', 'orders', 'WITH', 'invoice.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->orderBy('invoice.id', 'DESC')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheetIds(array $sheetIds): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
            ->where('invoice.sheet in (:sheetIds)')
            ->setParameter('sheetIds', $sheetIds)
            ->orderBy('invoice.id', 'ASC')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastInvoiceForEventPrefix(Prefix $prefix, $year)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('invoice')
            ->from(Invoice::class, 'invoice')
            ->where('invoice.prefix = :prefix')
            ->andWhere('invoice.invoicePrefix = :invoice_prefix')
            ->andWhere('invoice.invoiceYear = :invoice_year')
            ->orderBy('invoice.invoiceIncrement', 'DESC')
            ->setParameters([
                'prefix'         => $prefix,
                'invoice_prefix' => $prefix->getPrefix(),
                'invoice_year'   => $year,
            ])
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function isSheetInvoiced(Sheet $sheet)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('invoice.id')
            ->from(Invoice::class, 'invoice')
            ->where('invoice.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasInvoice(Sheet $sheet)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('invoice.id')
            ->from(Invoice::class, 'invoice')
            ->where('invoice.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->setMaxResults(1)
        ;

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
