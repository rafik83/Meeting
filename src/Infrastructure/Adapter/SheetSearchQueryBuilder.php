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
use Elastica\Query\MultiMatch;
use Elastica\Query\Nested;
use Elastica\Query\Range;
use Elastica\Query\Term;
use Proximum\Vimeet\Application\View\Catalog\PositionView;
use Proximum\Vimeet\Domain\Exception\Nomenclature\NomenclatureNotFoundException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\Type\TypeInterface;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Infrastructure\Elastica\AvailableLocales;
use Proximum\Vimeet\Infrastructure\Elastica\QueryBuilder\NomenclatureQueryBuilder;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\FollowerChoiceType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog\SearchType;

class SheetSearchQueryBuilder
{
    const BOOSTER_SHEET_NAME      = 5;
    const BOOSTER_LOCALE_CONTENT  = 3;
    const BOOSTER_DEFAULT_CONTENT = 2;

    // Percentage content minimum should match
    const CONTENT_MINIMUM_SHOULD_MATCH = 70;

    /**
     * @var BoolQuery
     */
    private $query;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var int
     */
    private $initialBooster = 1;

    /**
     * Array of nomenclature items with all nomenclature objective
     *
     * @var array
     */
    private $nomenclatureItems;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     * @param int    $initialBooster
     * @param array  $nomenclatureItems
     */
    public function __construct(
        Event $event,
        array $filters,
        $locale,
        $initialBooster = 1,
        $nomenclatureItems = []
    ) {
        $this->locale            = $locale;
        $this->initialBooster    = $initialBooster;
        $this->nomenclatureItems = $nomenclatureItems;

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
        $matchEvent = new Term();
        $matchEvent->setTerm('event', $event->getId());
        $this->query->addMust($matchEvent);
    }

    /**
     * Has owner
     *
     * @deprecated To be removed, used for dev reason
     */
    protected function hasOwner()
    {
        $rangeOwner = new Range();
        $rangeOwner->addField('owner', ['gt' => 0]);
        $this->query->addMust($rangeOwner);
    }

    /**
     * Has participant
     *
     * @deprecated To be removed, used for dev reason
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
        $this->filterByValidationState($filters);
        $this->filterByEnabled($filters);
        $this->filterByCompleted($filters);
        $this->filterByType($filters);
        $this->filterByCategory($filters);
        $this->filterByFollower($filters);
        $this->filterByPredefined($filters);
        $this->filterByRegisteredAt($filters);
        $this->filterByInCatalog($filters);
        $this->filterByOrganizationCategory($filters);
        $this->filterByLocalization($filters);
        $this->filterByPosition($filters);
        $this->filterByContent($filters);

        if (isset($filters[Constant::HAS_CART]) && true === $filters[Constant::HAS_CART]) {
            $this->filterHasCart();
        }

        if (isset($filters[Constant::NO_ORDER]) && true === $filters[Constant::NO_ORDER]) {
            $this->filterNoOrder();
        }

        if (isset($filters['boolean_filters'])) {
            $this->filterByBooleanFilter($filters['boolean_filters']);
        }

        if (isset($filters[SearchType::FILTER_OBJECTIVE])) {
            $this->filterByObjective($filters[SearchType::FILTER_OBJECTIVE]);
        }
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
     * @param array $filters
     */
    protected function filterByContent(array &$filters)
    {
        if (!isset($filters['content']) || null === $filters['content']) {
            return;
        }

        // Boost sheetname and content
        $fields = [
            sprintf('sheetName^%s', $this->initialBooster * self::BOOSTER_SHEET_NAME),
            sprintf('content^%s', $this->initialBooster * self::BOOSTER_DEFAULT_CONTENT),
        ];

        if (in_array($this->locale, AvailableLocales::getAvailableLocalesForContent())) {
            // If locale field is available
            $fields[] = sprintf('content_%s^%s', $this->locale, $this->initialBooster * self::BOOSTER_LOCALE_CONTENT);
        }

        $boolQuery = new BoolQuery();

        foreach (explode(',', $filters['content']) as $keyword) {
            $multiMatch = new MultiMatch();
            $multiMatch
                ->setFields($fields)
                ->setFuzziness(1)
                ->setQuery($keyword);

            $boolQuery->addShould($multiMatch);
        }

        $this->query->addMust($boolQuery);
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
        /** @var array|string $filters['state'] */
        if (isset($filters['state'])) {
            // Cast into array:
            $states = (array) $filters['state'];
            $filterByState = new BoolQuery();
            foreach ($states as $state) {
                if (in_array($state, Sheet::getAllStates())) {
                    $filterByState->addShould((new Term())->setTerm('state', $state));
                }
            }
            $this->query->addMust($filterByState);
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByValidationState(array &$filters)
    {
        if (isset($filters['validationState'])) {
            // Cast validationState into array:
            $validationStates = (array) $filters['validationState'];
            $filterByValidationState = new BoolQuery();
            foreach ($validationStates as $validationState) {
                if (in_array($validationState, Sheet::getAllValidationStates())) {
                    $filterByValidationState->addShould((new Term())->setTerm('validationState', $validationState));
                }
            }
            $this->query->addMust($filterByValidationState);
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByEnabled(array &$filters)
    {
        if (isset($filters['enabled'])) {
            $this->query->addMust((new Term())->setTerm('enabled', (bool)$filters['enabled']));
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByType(array &$filters)
    {
        if (isset($filters['type'])) {
            if ($filters['type'] instanceof Type) {
                $this->query->addMust((new Term())->setTerm('type', $filters['type']->getId()));

            } elseif (is_array($filters['type'])) {
                $filterByTypes = new BoolQuery();

                foreach ($filters['type'] as $type) {
                    $typeId = null;

                    if ($type instanceof TypeInterface) {
                        $typeId = $type->getId();
                    }

                    $filterByTypes->addShould((new Term())->setTerm('type', $typeId));
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
        if (isset($filters['category']) && !empty($filters['category'])) {
            $categories = $filters['category'];
            if ($categories instanceof Category) {
                $categories = [$categories];
            }
            $nested     = new Nested();
            $boolQuery  = new BoolQuery();
            foreach ($categories as $category) {
                if ($category instanceof Category) {
                    $matchQuery = new Match();
                    $matchQuery->setField('categories.id', $category->getId());
                    $boolQuery->addShould($matchQuery);
                }
            }

            $nested->setQuery($boolQuery)->setPath('categories');
            $this->query->addMust($nested);
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByFollower(array &$filters)
    {
        if (isset($filters['follower'])) {
            if ($filters['follower'] === FollowerChoiceType::UNASSIGNED_FOLLOWER) {
                $matchFollower = new Term();
                $matchFollower->setTerm('followUp', 0);
                $this->query->addMust($matchFollower);

                return;
            }

            $followers = $filters['follower'];
            if ($followers instanceof Admin) {
                $followers = [$followers];
            }
            $followerQuery = new BoolQuery();

            foreach ($followers as $follower) {
                if ($follower instanceof Admin) {
                    $matchFollower = new Term();
                    $matchFollower->setTerm('followUp', $follower->getId());
                    $followerQuery->addShould($matchFollower);
                }
            }
            $this->query->addMust($followerQuery);
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByPredefined(array &$filters)
    {
        if (isset($filters['predefined'])) {
            if ($filters['predefined'] === Constant::CREATED_TODAY) {
                $this->filterCreatedToday();
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
     * Handle the registeredAt filter ("Registered today" or "Registered this week")
     *
     * @param array $filters
     */
    protected function filterByRegisteredAt(array &$filters)
    {
        if (isset($filters['registeredAt'])) {
            if ($filters['registeredAt'] === Constant::CREATED_TODAY) {
                $this->filterCreatedToday();

                return;
            }
            if ($filters['registeredAt'] === Constant::CREATED_THIS_WEEK) {
                $this->filterCreatedThisWeek();

                return;
            }
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByCompleted(array &$filters)
    {
        if (isset($filters['completed'])) {
            $matchCompleted = new Term();
            $matchCompleted->setTerm('completed', $filters['completed']);

            $this->query->addMust($matchCompleted);
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByInCatalog(array &$filters)
    {
        if (isset($filters['inCatalog'])) {
            $matchInCatalog = new Term();
            $matchInCatalog->setTerm('inCatalog', $filters['inCatalog']);

            $this->query->addMust($matchInCatalog);
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByOrganizationCategory(array &$filters)
    {
        if (isset($filters['organizationCategory']) && is_array($filters['organizationCategory'])) {
            $matchOrganizationCategory = new BoolQuery();

            foreach ($filters['organizationCategory'] as $organizationCategory) {
                if ($organizationCategory instanceof OrganizationCategoryView) {
                    $matchOrganizationCategory->addShould(
                        (new Term)->setTerm('organizationCategory', $organizationCategory->key)
                    );
                }
            }

            $this->query->addMust($matchOrganizationCategory);
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByLocalization(array &$filters)
    {
        if (isset($filters['localization'])) {
            $localizations = explode(',', $filters['localization']);

            $boolQuery = new BoolQuery();

            foreach ($localizations as $localization) {
                if (strlen($localization) >= 2 && preg_match('/^[0-9]*$/', $localization)) {
                    $boolQuery->addShould(new Match('zipcode', $localization));
                } else {
                    $boolQuery->addShould(new Match('city', $localization));

                    $nested           = new Nested();
                    $nestedBoolQuery  = new BoolQuery();
                    $matchQuery       = new Match('country.label', $localization);
                    $matchLocaleQuery = new Match('country.locale', $this->locale);

                    $nestedBoolQuery->addMust($matchQuery);
                    $nestedBoolQuery->addMust($matchLocaleQuery);

                    $nested->setQuery($nestedBoolQuery)->setPath('country');
                    $boolQuery->addShould($nested);
                }
            }

            $this->query->addMust($boolQuery);
        }
    }

    /**
     * Created totay filter
     */
    protected function filterCreatedToday()
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
        $matchHasOrder = new Term();
        $matchHasOrder->setTerm('hasOrder', false);

        $this->query->addMust($matchHasOrder);
    }

    /**
     * Sheet with unpaid cart
     */
    protected function filterHasCart()
    {
        $matchHasCart = new Term();
        $matchHasCart->setTerm('hasCart', true);

        $this->query->addMust($matchHasCart);
    }

    /**
     * @param string|string[] $booleanFilters
     */
    protected function filterByBooleanFilter($booleanFilters)
    {
        if (empty($booleanFilters)) {
            return;
        }

        $booleanFilters = (array) $booleanFilters;

        $nested     = new Nested();
        $boolQuery  = new BoolQuery();

        foreach ($booleanFilters as $filter) {
            $matchQuery = new Match();
            $matchQuery->setField('booleanFilter.key', $filter);
            $boolQuery->addShould($matchQuery);
        }

        $nested->setQuery($boolQuery)->setPath('booleanFilter');
        $this->query->addMust($nested);
    }

    /**
     * @param array $filters
     */
    private function filterByPosition(array &$filters)
    {
        if (isset($filters['position']) && is_array($filters['position'])) {
            $nested = new Nested();
            $nested->setPath('participants');

            $matchPosition = new BoolQuery();

            foreach ($filters['position'] as $position) {
                if ($position instanceof PositionView) {
                    $matchPosition->addShould(
                        (new Term)->setTerm('participants.position', $position->getKey())
                    );
                }
            }

            $nested->setQuery($matchPosition);
            $this->query->addMust($nested);
        }
    }

    /**
     * @param array $objectives
     */
    private function filterByObjective(array $objectives)
    {
        $queryBuilder = new NomenclatureQueryBuilder($this->nomenclatureItems);

        try {
            if (in_array(Nomenclature::OBJECTIVE_NEED, $objectives) &&
                in_array(Nomenclature::OBJECTIVE_SUPPLY, $objectives)
            ) {
                $this->query->addMust($queryBuilder->filterBySupplyOrder());

                return;
            }

            if (in_array(Nomenclature::OBJECTIVE_NEED, $objectives)) {
                $this->query->addMust($queryBuilder->filterByNeed()->getQuery());
            }

            if (in_array(Nomenclature::OBJECTIVE_SUPPLY, $objectives) &&
                isset($this->nomenclatureItems[Nomenclature::OBJECTIVE_SUPPLY])
            ) {
                $this->query->addMust($queryBuilder->filterBySupply()->getQuery());
            }
        } catch (NomenclatureNotFoundException $exception) {
            return;
        }
    }
}
