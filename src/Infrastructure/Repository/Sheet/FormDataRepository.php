<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Sheet;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Repository\Sheet\FormDataRepositoryInterface;

class FormDataRepository implements FormDataRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(Sheet\FormData $formData): void
    {
        $this->entityManager->persist($formData);
        $this->entityManager->flush($formData);
    }

    public function update(Sheet\FormData $formData): void
    {
        $this->entityManager->flush($formData);
    }

    public function save(Sheet\FormData $formData): void
    {
        $this->entityManager->persist($formData);
        $this->entityManager->flush($formData);
    }

    public function getBySheetAndFormTemplate(Sheet $sheet, FormTemplate $formTemplate): ?Sheet\FormData
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('formData')
            ->from(Sheet\FormData::class, 'formData')
            ->where('formData.sheet = :sheet')
            ->andWhere('formData.formTemplate = :formTemplate')
            ->setParameter('sheet', $sheet)
            ->setParameter('formTemplate', $formTemplate)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
