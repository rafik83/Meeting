<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Sheet;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\EventInterface;
use Proximum\Vimeet\Domain\Model\Sheet\Template;
use Proximum\Vimeet\Domain\Repository\Sheet\TemplateRepositoryInterface;

class TemplateRepository implements TemplateRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * SpotRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager  = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(Template::class, 'template');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTemplate()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(Template::class, 'template')
            ->where('template.event IS NULL');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateForGivenEvents(array $events)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(Template::class, 'template')
            ->join('template.event', 'event', 'WITH', 'event.id IN (:events)')
            ->setParameter('events', array_map(function (EventInterface $event) { return $event->getId(); }, $events));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(Template $template)
    {
        $this->entityManager->persist($template);
        $this->entityManager->flush($template);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Template $template)
    {
        $this->entityManager->flush($template);
    }
}
