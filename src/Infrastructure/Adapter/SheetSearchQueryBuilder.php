<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Elastica\Query\Nested;
use Elastica\Query\Range;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\Model\Type;

class SheetSearchQueryBuilder
{
    /**
     * @var BoolQuery
     */
    private $query;

    /**
     * SheetSearchQuery constructor.
     *
     * @param Event $event
     * @param array $filters
     */
    public function __construct(Event $event, array $filters)
    {
        $this->query = new BoolQuery();

        $this->matchEvent($event);
        $this->hasOwner();
        $this->hasParticipant();
        $this->filter($filters);
    }

    /**
     * Get query
     *
     * @return BoolQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Match event
     *
     * @param Event $event
     */
    protected function matchEvent(Event $event)
    {
        $matchEvent = new Match();
        $matchEvent->setField('event', $event->getId());
        $this->query->addMust($matchEvent);
    }

    /**
     * Has owner
     */
    protected function hasOwner()
    {
        $rangeOwner = new Range();
        $rangeOwner->addField('owner', ['gt' => 0]);
        $this->query->addMust($rangeOwner);
    }

    /**
     * Has participant
     */
    protected function hasParticipant()
    {
        $range = new Range();
        $range->addField('participantNumber', ['gt' => 0]);
        $this->query->addMust($range);
    }

    /**
     * @param array $filters
     */
    public function filter(array $filters)
    {
        if (empty($filters)) {
            return;
        }

        $this->filterByText($filters);
        $this->filterByState($filters);
        $this->filterByType($filters);
        $this->filterByCategory($filters);
        $this->filterByFollower($filters);
        $this->filterByPredefined($filters);
    }

    /**
     * @param array $filters
     */
    protected function filterByText(array &$filters)
    {
        if (!isset($filters['sheetName']) || null === $filters['sheetName']) {
            return;
        }

        if (false !== strpos($filters['sheetName'], '@')) {
            $this->filterByParticipantEmail($filters['sheetName']);

            return;
        }

        $this->filterBySheetName($filters['sheetName']);
    }

    /**
     * @param $sheetName
     */
    protected function filterBySheetName($sheetName)
    {
        $match = new Match();
        $match
            ->setFieldQuery('sheetName', $sheetName)
            ->setFieldFuzziness('sheetName', 'AUTO')
            ->setFieldAnalyzer('sheetName', 'sheetAnalyzer');

        $this->query->addMust($match);
    }

    /**
     * @param string $email
     */
    protected function filterByParticipantEmail($email)
    {
        $match = new Match();
        $match->setField('participants.email', $email);


        $boolQuery = new BoolQuery();
        $boolQuery->addMust($match);

        $nested = new Nested();
        $nested->setQuery($boolQuery)->setPath('participants');

        $this->query->addMust($nested);
    }

    /**
     * @param array $filters
     */
    protected function filterByState(array &$filters)
    {
        if (isset($filters['state']) && in_array($filters['state'], Sheet::getAllStates())) {
            $match2 = new Match();
            $match2
                ->setFieldQuery('state', $filters['state']);

            $this->query->addMust($match2);
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByType(array &$filters)
    {
        if (isset($filters['type']) && $filters['type'] instanceof Type) {
            $matchType = new Match();
            $matchType
                ->setField('type', $filters['type']->getId());

            $this->query->addMust($matchType);
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByCategory(array &$filters)
    {
        if (isset($filters['category']) && $filters['category'] instanceof Category) {
            $nested     = new Nested();
            $boolQuery  = new BoolQuery();
            $matchQuery = new Match();
            $matchQuery->setField('categories.id', $filters['category']->getId());

            $nested->setQuery($boolQuery->addMust($matchQuery))->setPath('categories');
            $this->query->addMust($nested);
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByFollower(array &$filters)
    {
        if (isset($filters['follower']) && $filters['follower'] instanceof Admin) {
            $matchFollower = new Match();
            $matchFollower
                ->setField('followUp', $filters['follower']->getId());

            $this->query->addMust($matchFollower);
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByPredefined(array &$filters)
    {
        if (isset($filters['predefined'])) {
            if ($filters['predefined'] === Constant::CREATED_TODAY) {
                $this->filterCreatedTotay();
            } elseif ($filters['predefined'] === Constant::CREATED_THIS_WEEK) {
                $this->filterCreatedThisWeek();
            }
        }
    }

    /**
     * Created totay filter
     */
    protected function filterCreatedTotay()
    {
        $rangePredefinedDateBegin = new Range();
        $rangePredefinedDateEnd   = new Range();

        $rangePredefinedDateBegin
            ->addField('createdAt', ['gte' => (new \DateTime())->setTime(0, 0, 0)->format('c')]);
        $rangePredefinedDateEnd
            ->addField('createdAt', ['lte' => (new \DateTime())->setTime(23, 59, 59)->format('c')]);

        $this->query->addMust($rangePredefinedDateBegin);
        $this->query->addMust($rangePredefinedDateEnd);
    }

    /**
     * Created this week filter
     */
    protected function filterCreatedThisWeek()
    {
        $now       = new \DateTime();
        $dayOfWeek = $now->format('N');
        $beginWeek = clone $now;

        if ($dayOfWeek > 1) {
            $beginWeek->modify(sprintf('-%s day', $dayOfWeek - 1));
        }

        $endWeek = clone $beginWeek;
        $endWeek->modify('+6 day');

        $rangePredefinedDateBegin = new Range();
        $rangePredefinedDateEnd   = new Range();
        $rangePredefinedDateBegin->addField('createdAt', ['gte' => $beginWeek->setTime(0, 0, 0)->format('c')]);
        $rangePredefinedDateEnd->addField('createdAt', ['lte' => $endWeek->setTime(23, 59, 59)->format('c')]);

        $this->query->addMust($rangePredefinedDateBegin);
        $this->query->addMust($rangePredefinedDateEnd);
    }
}
