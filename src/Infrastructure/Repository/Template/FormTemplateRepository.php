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
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;

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

    /**
     * {@inheritdoc}
     */
    public function getBaseTemplates(): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(FormTemplate::class, 'template')
            ->where('template.event IS NULL');

        return $queryBuilder->getQuery()->getResult();
    }
}
