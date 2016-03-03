<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Paginator;

use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\PaginatedResult;

class Paginator
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param int          $page
     * @param int          $limit
     * @param string       $selector
     * @param string       $element
     *
     * @return array
     */
    public function getResultsAndTotal(QueryBuilder $queryBuilder, $page, $limit, $selector, $element = 'id')
    {
        $resultQueryBuilder = clone $queryBuilder;
        $countQueryBuilder  = clone $queryBuilder;
        $idsQueryBuilder    = clone $queryBuilder;

        $countQueryBuilder->select('COUNT(' . $selector .'.' . $element .')');
        $total = (int) $countQueryBuilder->getQuery()->getSingleScalarResult();

        $idsQueryBuilder
            ->select($selector .'.' . $element)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $ids = array_filter($idsQueryBuilder->getQuery()->getResult(), function ($id) {
            return isset($id['id']) ? $id['id'] : $id;
        });

        $results = $resultQueryBuilder
            ->andWhere($selector .'.' . $element . ' IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        return [$results, $total];
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param int          $page
     * @param int          $limit
     * @param string       $selector
     * @param string       $element
     *
     * @return PaginatedResult
     */
    public function paginate(QueryBuilder $queryBuilder, $page, $limit, $selector, $element = 'id')
    {
        list ($results, $total) = $this->getResultsAndTotal($queryBuilder, $page, $limit, $selector, $element);

        return new PaginatedResult($results, $page, $limit, $total);
    }
}
