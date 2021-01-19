<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Template;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Form\FormTemplateView;

class FormTemplateRepository implements FormTemplateRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(FormTemplate $template): void
    {
        $this->entityManager->persist($template);
        $this->entityManager->flush($template);
    }

    public function update(FormTemplate $template): void
    {
        $this->entityManager->flush($template);
    }

    public function findByEvent(Event $event): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('formTemplate')
            ->from(FormTemplate::class, 'formTemplate')
            ->where('formTemplate.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getPublishedFormTemplateViewByType(Type $type, string $locale): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf(
                    'NEW %s(formTemplate.id, formTemplateTranslation.title)',
                    FormTemplateView::class
                )
            )
            ->from(FormTemplate::class, 'formTemplate')
            ->join('formTemplate.types', 'type', 'WITH', 'type = :type and formTemplate.published = true')
            ->join(
                'formTemplate.translations',
                'formTemplateTranslation',
                'WITH',
                'formTemplateTranslation.locale = :locale'
            )
            ->setParameter('type', $type)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getById(int $formTemplateId): ?FormTemplate
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('formTemplate')
            ->from(FormTemplate::class, 'formTemplate')
            ->where('formTemplate.id = :formTemplateId')
            ->setParameter('formTemplateId', $formTemplateId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
