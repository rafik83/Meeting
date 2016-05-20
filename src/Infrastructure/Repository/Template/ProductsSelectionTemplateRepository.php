<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Template;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\EventInterface;
use Proximum\Vimeet\Domain\Model\Template\ProductsSelectionTemplate;
use Proximum\Vimeet\Domain\Repository\Template\ProductsSelectionTemplateRepositoryInterface;

class ProductsSelectionTemplateRepository implements ProductsSelectionTemplateRepositoryInterface
{
    /**
     * @var EntityManager
     */
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
    public function getTemplateForGivenEvents(array $events)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(ProductsSelectionTemplate::class, 'template')
            ->join('template.event', 'event', 'WITH', 'event.id IN (:events)')
            ->setParameter(
                'events',
                array_map(
                    function (EventInterface $event) {
                        return $event->getId();
                    },
                    $events
                )
            );

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(ProductsSelectionTemplate $productsSelectionTemplate)
    {
        $this->entityManager->persist($productsSelectionTemplate);
        $this->entityManager->flush($productsSelectionTemplate);
    }

    /**
     * {@inheritdoc}
     */
    public function set(ProductsSelectionTemplate $productsSelectionTemplate)
    {
        $this->entityManager->flush($productsSelectionTemplate);
    }
}
