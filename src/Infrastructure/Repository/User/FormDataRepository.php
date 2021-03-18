<?php

namespace Proximum\Vimeet\Infrastructure\Repository\User;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\FormData;
use Proximum\Vimeet\Domain\Repository\User\FormDataRepositoryInterface;

class FormDataRepository implements FormDataRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(FormData $formData): void
    {
        $this->entityManager->persist($formData);
        $this->entityManager->flush($formData);
    }

    public function update(FormData $formData): void
    {
        $this->entityManager->flush($formData);
    }

    public function save(FormData $formData): void
    {
        $this->entityManager->persist($formData);
        $this->entityManager->flush($formData);
    }

    public function getByUserAndFormTemplate(User $user, FormTemplate $formTemplate): ?FormData
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('formData')
            ->from(FormData::class, 'formData')
            ->where('formData.user = :user')
            ->andWhere('formData.formTemplate = :formTemplate')
            ->setParameter('user', $user)
            ->setParameter('formTemplate', $formTemplate)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getDataByEventIdAndUserId(int $eventId, int $userId): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('formData.data')
            ->from(FormData::class, 'formData')
            ->where('formData.user = :user')
            ->innerJoin('formData.formTemplate', 'formTemplate', 'WITH', 'formTemplate.event = :event')
            ->setParameters([
                'user' => $userId,
                'event' => $eventId,
            ])
            ->getQuery()
            ->getResult();
    }
}
