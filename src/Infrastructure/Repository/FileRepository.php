<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;

class FileRepository implements FileRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * FileRepository constructor.
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
    public function add(File $file)
    {
        $this->entityManager->persist($file);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('file')
            ->from(File::class, 'file')
            ->where('file.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(File $file)
    {
        $this->entityManager->remove($file);
        $this->entityManager->flush($file);
    }

    public function findExpiredFiles(\DateTimeInterface $dateTime): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('file')
            ->from(File::class, 'file')
            ->where('file.type != :unknownType')
            ->andWhere('file.createdAt <= :dateTime')
            ->setParameters([
                'unknownType' => File::TYPE_UNKNOWN,
                'dateTime' => $dateTime->modify('-48 hours'),
            ])
            ->getQuery()
            ->getResult();
    }
}
