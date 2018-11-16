<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

    public function getFormTemplateViewByType(Type $type, string $locale): array
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
}
