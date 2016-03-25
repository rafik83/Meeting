<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */
namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Adapter;

use Elastica\Query\Bool;
use Elastica\Query\Match;
use Elastica\Query\MatchAll;
use FOS\ElasticaBundle\Finder\FinderInterface;
use Proximum\Vimeet\Domain\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;

class SheetSearchAdapter implements SheetSearchAdapterInterface
{
    /**
     * @var FinderInterface Elastica finder
     */
    private $finder;

    /**
     * Constructor
     *
     * @param FinderInterface $finder
     */
    public function __construct(FinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * {@inheritdoc}
     */
    public function find(Event $event, array $filters, $page, $limit, $locale)
    {

        if (!empty($filters)) {
            $query = new Bool();

            if (isset($filters['sheetName']) && $filters['sheetName'] !== null) {
                $fieldQuery = new Match();
                $fieldQuery
                    ->setFieldQuery('sheetName', $filters['sheetName'])
                    ->setFieldFuzziness('sheetName', 'AUTO')
                    ->setFieldAnalyzer('sheetName', 'sheetAnalyzer');

                $query->addShould($fieldQuery);
            }

            if (isset($filters['sheetName']) && $filters['sheetName'] !== null) {

            }
        } else {
            $query = new MatchAll();
        }

        return $this->finder->find($query, 100);
    }
}
