<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Doctrine\ORM\QueryBuilder\Sheet;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\Category;

class SearchQueryBuilder extends QueryBuilder
{
    /**
     * {@inheritdoc}
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);

        $this
            ->select('sheet')
            ->from('Entity:Sheet', 'sheet')
            ->join('sheet.type', 'type');
    }

    /**
     * @param Category $category
     *
     * @return SearchQueryBuilder
     */
    public function withCategory(Category $category)
    {
        $filters = $category->getFilters();

        if (isset($filters['type'])) {
            $this
                ->andWhere('sheet.type IN (:type)')
                ->setParameter('type', $filters['type']);
        }

        return $this;
    }
}
