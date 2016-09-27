<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

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
use Proximum\Vimeet\Domain\View\Catalog\TypeView;

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
        $this->filterByCompleted($filters);
        $this->filterByType($filters);
        $this->filterByCategory($filters);
        $this->filterByFollower($filters);
        $this->filterByPredefined($filters);
        $this->filterByInCatalog($filters);
    }

    /**
     * @param array $filters
     */
    protected function filterByText(array &$filters)
    {
        if (!isset($filters['text']) || null === $filters['text']) {
            return;
        }

        if (false !== strpos($filters['text'], '@')) {
            $this->filterByParticipantEmail($filters['text']);

            return;
        }

        $this->filterBySheetNameOrParticipantLastname($filters['text']);
    }

    /**
     * @param string $text
     */
    protected function filterBySheetNameOrParticipantLastname($text)
    {
        $filterBySheetNameOrParticipantLastnameQuery = new BoolQuery();

        $matchSheetName = new Match();
        $matchSheetName
            ->setFieldQuery('sheetName', $text)
            ->setFieldFuzziness('sheetName', 'AUTO');

        $filterBySheetNameOrParticipantLastnameQuery->addShould($matchSheetName);

        $matchLastname = new Match();
        $matchLastname
            ->setFieldQuery('participants.lastname', $text)
            ->setFieldFuzziness('participants.lastname', 'AUTO');

        $boolQuery = new BoolQuery();
        $boolQuery->addMust($matchLastname);

        $nestedParticipants = new Nested();
        $nestedParticipants->setQuery($boolQuery)->setPath('participants');

        $filterBySheetNameOrParticipantLastnameQuery->addShould($nestedParticipants);

        $this->query->addMust($filterBySheetNameOrParticipantLastnameQuery);
    }

    /**
     * @param string $email
     */
    protected function filterByParticipantEmail($email)
    {
        $matchEmail = new Match();
        $matchEmail
            ->setFieldQuery('participants.email', $email);

        $boolQuery = new BoolQuery();
        $boolQuery->addMust($matchEmail);

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
        if (isset($filters['type'])) {
            if ($filters['type'] instanceof Type) {
                $matchType = new Match();
                $matchType->setField('type', $filters['type']->getId());
                $this->query->addMust($matchType);

            } elseif (is_array($filters['type'])) {
                $filterByTypes = new BoolQuery();

                foreach ($filters['type'] as $type) {
                    if ($type instanceof TypeView) {
                        $filterByTypes->addShould(new Match('type', $type->id));
                    } elseif ($type instanceof Type) {
                        $filterByTypes->addShould(new Match('type', $type->getId()));
                    }
                }

                $this->query->addMust($filterByTypes);
            }


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
            } elseif ($filters['predefined'] === Constant::NO_ORDER) {
                $this->filterNoOrder();
            } elseif ($filters['predefined'] === Constant::HAS_CART) {
                $this->filterHasCart();
            } else {
                $this->filterByBooleanFilter($filters['predefined']);
            }
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByCompleted(array &$filters)
    {
        if (isset($filters['completed'])) {
            $matchCompleted = new Match();
            $matchCompleted->setField('completed', $filters['completed']);

            $this->query->addMust($matchCompleted);
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByInCatalog(array &$filters)
    {
        if (isset($filters['inCatalog'])) {
            $matchInCatalog = new Match();
            $matchInCatalog->setField('inCatalog', $filters['inCatalog']);

            $this->query->addMust($matchInCatalog);
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

    /**
     * Sheet with no order
     */
    protected function filterNoOrder()
    {
        $matchHasOrder = new Match();
        $matchHasOrder->setField('hasOrder', false);

        $this->query->addMust($matchHasOrder);
    }

    /**
     * Sheet with unpaid cart
     */
    protected function filterHasCart()
    {
        $matchHasCart = new Match();
        $matchHasCart->setField('hasCart', true);

        $this->query->addMust($matchHasCart);
    }

    /**
     * @param string $predefined
     */
    private function filterByBooleanFilter($predefined)
    {
        $nested     = new Nested();
        $boolQuery  = new BoolQuery();
        $matchQuery = new Match();
        $matchQuery->setField('booleanFilter.key', $predefined);

        $nested->setQuery($boolQuery->addMust($matchQuery))->setPath('booleanFilter');
        $this->query->addMust($nested);
    }
}
