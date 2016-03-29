<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Adapter;

use Elastica\Query;
use Elastica\Query\Bool;
use Elastica\Query\Match;
use Elastica\Query\Nested;
use Elastica\Query\Range;
use Elastica\QueryBuilder;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Proximum\Vimeet\Domain\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\Model\Type;

class SheetSearchAdapter implements SheetSearchAdapterInterface
{
    /**
     * @var PaginatedFinderInterface Elastica finder
     */
    private $finder;

    /**
     * Constructor
     *
     * @param PaginatedFinderInterface $finder
     */
    public function __construct(PaginatedFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * {@inheritdoc}
     */
    public function find(Event $event, array $filters, $page, $limit, $locale)
    {
        $query = new Query();
        $bool  = new Bool();

        $matchEvent = new Match();
        $matchEvent->setField('event', $event->getId());
        $bool->addMust($matchEvent);

        $rangeOwner = new Range();
        $rangeOwner->addField('owner', ['gt' => 0]);
        $bool->addMust($rangeOwner);

        if (!empty($filters)) {
            if (isset($filters['sheetName']) && $filters['sheetName'] !== null) {
                $match = new Match();
                $match
                    ->setFieldQuery('sheetName', $filters['sheetName'])
                    ->setFieldFuzziness('sheetName', 'AUTO')
                    ->setFieldAnalyzer('sheetName', 'sheetAnalyzer');

                $bool->addMust($match);
            }

            if (isset($filters['state']) && in_array($filters['state'], [Sheet::STATE_COMPLETE, Sheet::STATE_INCOMPLETE,Sheet::STATE_VALIDATED])) {
                $match2 = new Match();
                $match2
                    ->setFieldQuery('state', $filters['state']);

                $bool->addMust($match2);
            }

            if (isset($filters['type']) && $filters['type'] instanceof Type) {
                $matchType = new Match();
                $matchType
                    ->setField('type', $filters['type']->getId());

                $bool->addMust($matchType);
            }

            if (isset($filters['category']) && $filters['category'] instanceof Category) {
                $nested = new Nested();
                $boolQuery = new Bool();
                $matchQuery = new Match();
                $matchQuery->setField('categories.id', $filters['category']->getId());

                $nested->setQuery($boolQuery->addMust($matchQuery))->setPath('categories');
                $bool->addMust($nested);
            }

            if (isset($filters['follower']) && $filters['follower'] instanceof Admin) {
                $matchFollower = new Match();
                $matchFollower
                    ->setField('followUp', $filters['follower']->getId());

                $bool->addMust($matchFollower);
            }

            if (isset($filters['predefined'])) {
                if ($filters['predefined'] === Constant::CREATED_TODAY) {
                    $rangePredefinedDateBegin = new Range();
                    $rangePredefinedDateEnd   = new Range();

                    $rangePredefinedDateBegin
                        ->addField('createdAt', ['gte' => (new \DateTime())->setTime(0, 0, 0)->format('c')]);
                    $rangePredefinedDateEnd
                        ->addField('createdAt', ['lte' => (new \DateTime())->setTime(23, 59, 59)->format('c')]);

                    $bool->addMust($rangePredefinedDateBegin);
                    $bool->addMust($rangePredefinedDateEnd);
                } elseif ($filters['predefined'] === Constant::CREATED_THIS_WEEK) {
                    $now = new \DateTime();
                    $dayOfWeek = $now->format('N');

                    $beginWeek = clone $now;

                    if ($dayOfWeek > 1) {
                        $beginWeek->modify(sprintf('-%s day', $dayOfWeek - 1));
                    }

                    $endWeek = clone $beginWeek;
                    $endWeek->modify('+6 day');

                    $rangePredefinedDateBegin = new Range();
                    $rangePredefinedDateEnd   = new Range();
                    $rangePredefinedDateBegin
                        ->addField('createdAt', ['gte' => $beginWeek->setTime(0, 0, 0)->format('c')]);
                    $rangePredefinedDateEnd
                        ->addField('createdAt', ['lte' => $endWeek->setTime(23, 59, 59)->format('c')]);

                    $bool->addMust($rangePredefinedDateBegin);
                    $bool->addMust($rangePredefinedDateEnd);
                }
            }
        }

        $range = new Range();
        $range->addField('participantNumber', ['gt' => 0]);
        $bool->addMust($range);

        $query->setQuery($bool);

        $result = $this->finder
            ->findPaginated($query)
            ->setMaxPerPage($limit)
            ->setCurrentPage($page);

        return new PaginatedResult($result->getCurrentPageResults(), $page, $limit, $result->getNbResults());
    }
}
