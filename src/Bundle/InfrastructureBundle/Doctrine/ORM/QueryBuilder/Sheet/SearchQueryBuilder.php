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
            ->join('sheet.type', 'type')
            ->join('type.categories', 'category');
    }

    /**
     * @param Category|int $category
     *
     * @return SearchQueryBuilder
     */
    public function withCategory($category)
    {
        $this
            ->andWhere('category = :category')
            ->setParameter('category', $category);

        return $this;
    }

    /**
     * @param array $types
     *
     * @return SearchQueryBuilder
     */
    public function withTypes(array $types)
    {
        $this
            ->andWhere('type IN (:types)')
            ->setParameter('types', $types);

        return $this;
    }
}
