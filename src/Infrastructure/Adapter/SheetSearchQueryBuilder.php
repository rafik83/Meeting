<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Elastica\Filter\Exists;
use Elastica\Query\BoolQuery;
use Elastica\Query\Filtered;
use Elastica\Query\Match;
use Elastica\Query\Nested;
use Elastica\Query\Range;
use Elastica\Query\Term;
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Application\View\Catalog\PositionView;
use Proximum\Vimeet\Application\View\Sheet\CountryView;
use Proximum\Vimeet\Domain\Admin\Follower\FollowerConstant;
use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Exception\Nomenclature\NomenclatureNotFoundException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\CatalogConstant;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Sheet\Availability\ConfirmationStatus;
use Proximum\Vimeet\Domain\Sheet\Phone\ValidationStatus;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\Type\TypeInterface;
use Proximum\Vimeet\Domain\View\Catalog\CategoryView;
use Proximum\Vimeet\Domain\View\Catalog\OrganizationCategoryView;
use Proximum\Vimeet\Infrastructure\Elastica\QueryBuilder\ContentQueryBuilder;
use Proximum\Vimeet\Infrastructure\Elastica\QueryBuilder\NomenclatureQueryBuilder;

class SheetSearchQueryBuilder
{
    /** @var BoolQuery */
    private $query;

    /** @var string */
    private $locale;

    /** @var int */
    private $initialBooster = 1;

    /**
     * Array of nomenclature items with all nomenclature objective
     *
     * @var array
     */
    private $nomenclatureItems;

    /** @var array of available slot ids */
    private $availableSlots;

    /**
     * @param AvailableSlotView[] $availableSlots
     * @param int[] $prefilteredSheetIds
     */
    public function __construct(
        Event $event,
        array $filters,
        string $locale,
        int $initialBooster = 1,
        array $nomenclatureItems = [],
        array $availableSlots = [],
        array $sheetsToExclude = [],
        ?array $prefilteredSheetIds = null
    ) {
        $this->locale            = $locale;
        $this->initialBooster    = $initialBooster > 0 ? $initialBooster : 1;
        $this->nomenclatureItems = $nomenclatureItems;
        $this->availableSlots    = $availableSlots;

        $this->query = new BoolQuery();
        $this->matchEvent($event);
        $this->filter($filters);
        $this->excludeSheets($sheetsToExclude);
        $this->restrictToSheets($prefilteredSheetIds);
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
     * @param array $filters
     */
    public function filter(array $filters)
    {
        if (empty($filters)) {
            return;
        }

        // Remove empty filters before apply elastica filters
        $this->discardEmptyFilters($filters);

        $this->filterByState($filters);
        $this->filterByValidationState($filters);
        $this->filterByEnabled($filters);
        $this->filterByCompleted($filters);
        $this->filterByType($filters);
        $this->filterByCategory($filters);
        $this->filterByAvailableSlotIds($filters);
        $this->filterByFollower($filters);
        $this->filterByCommercialStatus($filters);
        $this->filterByPredefined($filters);
        $this->filterByRegisteredAt($filters);
        $this->filterByInCatalog($filters);
        $this->filterByOrganizationCategory($filters);
        $this->filterByLocalization($filters);
        $this->filterByPosition($filters);
        $this->filterByContent($filters);
        $this->filterByHasHappeningParticipation($filters);
        $this->filterByHasScheduledMeeting($filters);
        $this->filterByHasInvoice($filters);
        $this->filterByImported($filters);
        $this->filterByCanceledAttendance($filters);
        $this->filterByHasGroup($filters);
        $this->filterByHasSpot($filters);
        $this->filterByOrderStatus($filters);
        $this->filterByReminderDate($filters);
        $this->filterByCountry($filters);
        $this->filterByTagFilters($filters);

        if (isset($filters[Constant::FILLED_FILTER])) {
            $this->filterByFilledFilter($filters[Constant::FILLED_FILTER]);
        }

        if (isset($filters[Constant::HAS_CART]) && true === $filters[Constant::HAS_CART]) {
            $this->filterHasCart(true);
        }

        if (isset($filters['boolean_filters'])) {
            $this->filterByBooleanFilter($filters['boolean_filters']);
        }

        if (isset($filters[SearchFields::FILTER_OBJECTIVE])) {
            $this->filterByObjective($filters[SearchFields::FILTER_OBJECTIVE]);
        }

        if (isset($filters[Constant::HAS_CART])) {
            $this->filterHasCart($filters[Constant::HAS_CART]);
        }

        if (isset($filters['hasRemainingToPay'])) {
            $this->filterByHasRemainingToPay($filters['hasRemainingToPay']);
        }

        if (isset($filters['hasNoMeetingRequest']) && is_bool($filters['hasNoMeetingRequest'])) {
            $this->filterByNoMeetingRequest($filters['hasNoMeetingRequest']);
        }

        if (isset($filters['hasPendingMeetingPropositions']) && is_bool($filters['hasPendingMeetingPropositions'])) {
            $this->filterByHasPendingMeetingProposition($filters['hasPendingMeetingPropositions']);
        }

        if (isset($filters['agendaConfirmedStatus'])
            && in_array($filters['agendaConfirmedStatus'], Sheet::AGENDA_CONFIRMED_STATUS)
        ) {
            $this->filterByAgendaConfirmedStatus($filters['agendaConfirmedStatus']);
        }

        $this->filterByPhoneValidationStatus($filters);
        $this->filterByAvailabilityConfirmationStatus($filters);
    }

    /**
     * @param array $filters
     */
    private function discardEmptyFilters(array &$filters)
    {
        $filters = array_filter($filters, function ($filter) {
            return '' !== $filter;
        });
    }

    /**
     * @param array $filters
     */
    protected function filterByContent(array &$filters): void
    {
        if (!isset($filters[SearchFields::FILTER_CONTENT])
            || empty($filters[SearchFields::FILTER_CONTENT])
        ) {
            return;
        }

        $search = explode('|', $filters[SearchFields::FILTER_CONTENT]);

        $builder = new ContentQueryBuilder();
        $boolQuery = new BoolQuery();

        foreach ($search as $value) {
            $boolQuery->addShould(
                $builder->getElasticaQuery($value, $this->locale)
            );
        }

        $this->query->addMust($boolQuery);
    }

    /**
     * @param array $filters
     */
    protected function filterByState(array &$filters)
    {
        /** @var array|string $filters ['state'] */
        if (!isset($filters['state']) || (\is_array($filters['state']) && empty($filters['state']))) {
            return;
        }

        // Cast into array:
        $states        = (array) $filters['state'];
        $filterByState = new BoolQuery();

        foreach ($states as $state) {
            if (in_array($state, Sheet::getAllStates())) {
                $filterByState->addShould((new Term())->setTerm('state', $state));
            }
        }

        $this->query->addMust($filterByState);
    }

    /**
     * @param array $filters
     */
    protected function filterByValidationState(array &$filters)
    {
        if (!isset($filters['validationState']) || empty($filters['validationState'])) {
            return;
        }

        // Cast validationState into array:
        $validationStates        = (array) $filters['validationState'];
        $filterByValidationState = new BoolQuery();
        foreach ($validationStates as $validationState) {
            if (in_array($validationState, Sheet::getAllValidationStates())) {
                $filterByValidationState->addShould((new Term())->setTerm('validationState', $validationState));
            }
        }
        $this->query->addMust($filterByValidationState);
    }

    /**
     * @param array $filters
     */
    protected function filterByEnabled(array &$filters)
    {
        if (!isset($filters['enabled'])) {
            return;
        }

        $this->query->addMust((new Term())->setTerm('enabled', (bool) $filters['enabled']));
    }

    /**
     * @param array $filters
     */
    protected function filterByType(array &$filters)
    {
        if (!isset($filters['type']) || empty($filters['type'])) {
            return;
        }

        if ($filters['type'] instanceof Type) {
            $this->query->addMust((new Term())->setTerm('type', $filters['type']->getId()));
        } elseif (is_array($filters['type'])) {
            $filterByTypes = new BoolQuery();

            foreach ($filters['type'] as $type) {
                $typeId = null;

                if ($type instanceof TypeInterface) {
                    $typeId = $type->getId();
                } elseif (is_int($type) || is_string($type)) {
                    $typeId = (int) $type;
                }

                if (null !== $typeId) {
                    $filterByTypes->addShould((new Term())->setTerm('type', $typeId));
                }
            }

            $this->query->addMust($filterByTypes);
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByCategory(array &$filters)
    {
        if (!isset($filters['categories']) || empty($filters['categories'])) {
            return;
        }

        $nested = new Nested();
        $nested->setPath('categories');

        $matchId = new BoolQuery();
        foreach ($filters['categories'] as $category) {
            $id = null;

            if ($category instanceof Category || $category instanceof CategoryView) {
                $id = $category->getId();
            } elseif (is_int($category) || is_string($category)) {
                $id = (int) $category;
            }

            if (null !== $category) {
                $matchId->addShould((new Term())->setTerm('categories.id', $id));
            }
        }

        $nested->setQuery($matchId);
        $this->query->addMust($nested);
    }

    /**
     * @param array $filters
     */
    protected function filterByAvailableSlotIds(array &$filters)
    {
        if (!isset($filters[SearchFields::FILTER_AVAILABLE_SLOT_IDS])
            || empty($filters[SearchFields::FILTER_AVAILABLE_SLOT_IDS])
            || CatalogConstant::AVAILABLE_SLOT_IDS_FILTER_EVERYONE === $filters[SearchFields::FILTER_AVAILABLE_SLOT_IDS]
        ) {
            return;
        }

        $filterAvailableSlotChoice = $filters[SearchFields::FILTER_AVAILABLE_SLOT_IDS];

        $nested = new Nested();
        $nested->setPath('availableSlotIds');

        $matchSlot = new BoolQuery();

        if (!empty($filters[SearchFields::FILTER_BY_SPECIFIC_SLOT])
            && CatalogConstant::AVAILABLE_SLOT_IDS_FILTER_SLOT === $filterAvailableSlotChoice
        ) {
            $matchSlot->addShould(
                (new Term())->setTerm('availableSlotIds.id', $filters[SearchFields::FILTER_BY_SPECIFIC_SLOT])
            );
        } elseif (CatalogConstant::AVAILABLE_SLOT_IDS_FILTER_AVAILABLE === $filterAvailableSlotChoice) {
            /** @var AvailableSlotView $availableSlot */
            foreach ($this->availableSlots as $availableSlot) {
                $matchSlot->addShould(
                    (new Term())->setTerm('availableSlotIds.id', $availableSlot->id)
                );
            }
        }

        $nested->setQuery($matchSlot);

        $this->query->addMust($nested);
    }

    /**
     * @param array $filters
     */
    protected function filterByFollower(array &$filters)
    {
        if (!isset($filters['follower']) || empty($filters['follower'])) {
            return;
        }

        $followers = $filters['follower'];

        $followerQuery = new BoolQuery();

        foreach ($followers as $follower) {
            if (FollowerConstant::UNASSIGNED_FOLLOWER === $follower) {
                $matchFollower = new Term();
                $matchFollower->setTerm('followUp', 0);
                $followerQuery->addShould($matchFollower);
            } elseif ($follower instanceof Admin) {
                $matchFollower = new Term();
                $matchFollower->setTerm('followUp', $follower->getId());
                $followerQuery->addShould($matchFollower);
            }
        }

        $this->query->addMust($followerQuery);
    }

    /**
     * @param array $filters
     */
    protected function filterByPredefined(array &$filters)
    {
        if (!isset($filters['predefined'])) {
            return;
        }

        if (Constant::CREATED_TODAY === $filters['predefined']) {
            $this->filterCreatedToday();
        } elseif (Constant::CREATED_THIS_WEEK === $filters['predefined']) {
            $this->filterCreatedThisWeek();
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
            if (Constant::CREATED_TODAY === $filters['registeredAt']) {
                $this->filterCreatedToday();

                return;
            }
            if (Constant::CREATED_THIS_WEEK === $filters['registeredAt']) {
                $this->filterCreatedThisWeek();

                return;
            }
        }
    }

    /**
     * Handle the reminderDate filter
     *
     * @param array $filters
     */
    protected function filterByReminderDate(array &$filters): void
    {
        if (isset($filters['reminderDate']['begin'], $filters['reminderDate']['end'])
            && $filters['reminderDate']['begin'] instanceof \DateTime
            && $filters['reminderDate']['end'] instanceof \DateTime
        ) {
            $rangePredefinedDateBegin = new Range();
            $rangePredefinedDateEnd = new Range();

            $rangePredefinedDateBegin
                ->addField('reminderDate', ['gte' => (clone $filters['reminderDate']['begin'])->setTime(0, 0, 0)->format('c')]);
            $rangePredefinedDateEnd
                ->addField('reminderDate', ['lte' => (clone $filters['reminderDate']['end'])->setTime(23, 59, 59)->format('c')]);

            $this->query->addMust($rangePredefinedDateBegin);
            $this->query->addMust($rangePredefinedDateEnd);
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
            $matchInCatalog->setTerm('inCatalog', (bool) $filters['inCatalog']);

            $this->query->addMust($matchInCatalog);
        }
    }

    /**
     * @param array $filters
     */
    protected function filterByOrganizationCategory(array &$filters)
    {
        if (!isset($filters['organizationCategory'])
            || !is_array($filters['organizationCategory'])
            || empty($filters['organizationCategory'])
        ) {
            return;
        }

        $matchOrganizationCategory = new BoolQuery();

        foreach ($filters['organizationCategory'] as $organizationCategory) {
            if ($organizationCategory instanceof OrganizationCategoryView) {
                $matchOrganizationCategory->addShould(
                    (new Term())->setTerm('organizationCategory', $organizationCategory->key)
                );
            }
        }

        $this->query->addMust($matchOrganizationCategory);
    }

    /**
     * @param array $filters
     */
    protected function filterByLocalization(array &$filters)
    {
        if (!isset($filters['localization'])
            || empty($filters['localization'])
        ) {
            return;
        }

        $localizations = explode('|', $filters['localization']);

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

    protected function filterByCountry(array &$filters): void
    {
        if (!isset($filters['country'])
            || empty($filters['country'])
        ) {
            return;
        }

        $countries = $filters['country'];

        $boolQuery = new BoolQuery();

        foreach ($countries as $country) {
            $matchQuery = new Match('countryCode', $country instanceof CountryView ? $country->code : $country);
            $boolQuery->addShould($matchQuery);
        }

        $this->query->addMust($boolQuery);
    }

    protected function filterByFilledFilter(array $filledFilters): void
    {
        foreach ($filledFilters as $key => $values) {
            if (!$values) {
                continue;
            }

            $boolQuery = new BoolQuery();

            foreach ($values as $filter) {
                $subBoolQuery = (new BoolQuery())
                    ->addMust((new Term())->setTerm('filledFilter.key', $key))
                    ->addMust((new Term())->setTerm('filledFilter.status', $filter));

                $boolQuery->addShould($subBoolQuery);
            }

            $nestedQuery = new Nested();
            $nestedQuery->setPath('filledFilter');
            $nestedQuery->setQuery($boolQuery);
            $this->query->addMust($nestedQuery);
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
     * Sheet with or without unpaid cart
     *
     * @param bool $hasCart
     */
    protected function filterHasCart($hasCart)
    {
        $matchHasCart = new Term();
        $matchHasCart->setTerm('hasCart', $hasCart);

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

        foreach ($booleanFilters as $key => $isFiltered) {
            $boolQuery = new BoolQuery();
            $boolQuery->addMust((new Match())->setField('booleanFilter.key', $key));

            $nested = new Nested();
            $nested->setQuery($boolQuery)->setPath('booleanFilter');

            if (true === $isFiltered) {
                $this->query->addMust($nested);
            } elseif (false === $isFiltered) {
                $this->query->addMustNot($nested);
            }
        }
    }

    /**
     * Filter sheet with canceled attendance
     *
     * @see Sheet::attend()
     *
     * @param array $filters
     */
    protected function filterByCanceledAttendance(array &$filters)
    {
        if (!isset($filters['cancelAttendance'])) {
            return;
        }

        $matchAttend = new Term();
        $matchAttend->setTerm('attend', !$filters['cancelAttendance']);
        $this->query->addMust($matchAttend);
    }

    /**
     * Filter sheet with group
     *
     * @see Sheet::hasGroup()
     *
     * @param array $filters
     */
    protected function filterByHasGroup(array &$filters)
    {
        if (!isset($filters['hasGroup'])) {
            return;
        }

        $matchHasGroup = new Term();
        $matchHasGroup->setTerm('hasGroup', (bool) $filters['hasGroup']);
        $this->query->addMust($matchHasGroup);
    }

    /**
     * Filter sheet with spot
     *
     * @param array $filters
     */
    protected function filterByHasSpot(array &$filters)
    {
        if (!isset($filters['hasSpot'])) {
            return;
        }

        $matchHasGroup = new Term();
        $matchHasGroup->setTerm('hasSpot', (bool) $filters['hasSpot']);
        $this->query->addMust($matchHasGroup);
    }

    /**
     * @param array $filters
     */
    private function filterByPosition(array &$filters)
    {
        if (!isset($filters['position']) || !is_array($filters['position']) || empty($filters['position'])) {
            return;
        }

        $nested = new Nested();
        $nested->setPath('participants');

        $matchPosition = new BoolQuery();

        foreach ($filters['position'] as $position) {
            if ($position instanceof PositionView) {
                $matchPosition->addShould(
                    (new Term())->setTerm('participants.position', $position->getKey())
                );
            }
        }

        $nested->setQuery($matchPosition);
        $this->query->addMust($nested);
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

    /**
     * @param array $filters
     */
    private function filterByHasHappeningParticipation(array &$filters)
    {
        if (!isset($filters['hasHappeningParticipation'])) {
            return;
        }

        $this
            ->query
            ->addMust((new Term())
            ->setTerm('hasHappeningParticipation', (bool) $filters['hasHappeningParticipation']))
        ;
    }

    /**
     * @param array $filters
     */
    private function filterByHasScheduledMeeting(array &$filters)
    {
        if (!isset($filters['hasScheduledMeeting'])) {
            return;
        }

        $this
            ->query
            ->addMust((new Term())
            ->setTerm('hasScheduledMeeting', (bool) $filters['hasScheduledMeeting']))
        ;
    }

    /**
     * @param array $filters
     */
    private function filterByHasInvoice(array &$filters)
    {
        if (!isset($filters['hasInvoice'])) {
            return;
        }

        $this->query->addMust((new Term())->setTerm('hasInvoice', (bool) $filters['hasInvoice']));
    }

    /**
     * If participant has remaning to pay, greater than 0,
     * if not, less than equal 0
     *
     * @param bool $hasRemainingToPay
     */
    private function filterByHasRemainingToPay($hasRemainingToPay)
    {
        $positiveRange = new Range();

        $parameters = true === $hasRemainingToPay ? ['gt' => 0] : ['lte' => 0];

        $positiveRange->addField('remainingToPay', $parameters);
        $this->query->addMust($positiveRange);
    }

    private function filterByNoMeetingRequest(bool $hasNoMeetingRequest)
    {
        $this->query->addMust((new Term())->setTerm('hasMeetingRequest', !$hasNoMeetingRequest));
    }

    private function filterByHasPendingMeetingProposition(bool $hasPendingMeetingProposition)
    {
        $this->query->addMust((new Term())->setTerm('hasPendingMeetingProposition', $hasPendingMeetingProposition));
    }

    /**
     * @param array $filters
     */
    private function filterByImported(array &$filters)
    {
        if (!isset($filters[Constant::FILTER_IMPORTED]) || empty($filters[Constant::FILTER_IMPORTED])) {
            return;
        }

        $importedQuery = new BoolQuery();

        if (Constant::IMPORTED === $filters[Constant::FILTER_IMPORTED]) {
            $importedQuery->addMust($this->isImported(true));
        }

        if (Constant::IMPORTED_WITH_CONNECTION === $filters[Constant::FILTER_IMPORTED]) {
            $importedQuery->addMust($this->isImported(true));
            $importedQuery->addMust($this->hasConnectionFilter());
        }

        if (Constant::IMPORTED_WITHOUT_CONNECTION === $filters[Constant::FILTER_IMPORTED]) {
            $importedQuery->addMust($this->isImported(true));
            $importedQuery->addMustNot($this->hasConnectionFilter());
        }

        if (Constant::NOT_IMPORTED === $filters[Constant::FILTER_IMPORTED]) {
            $importedQuery->addMust($this->isImported(false));
        }

        $this->query->addMust($importedQuery);
    }

    /**
     * @param bool $imported
     *
     * @return Term
     */
    private function isImported($imported)
    {
        return (new Term())->setTerm('imported', $imported);
    }

    /**
     * @return Filtered
     */
    private function hasConnectionFilter()
    {
        return (new Filtered())->setFilter(new Exists('lastLoginAt'));
    }

    /**
     * @param array $filters
     */
    private function filterByOrderStatus(array $filters)
    {
        if (empty($filters[Constant::ORDER_STATUS])) {
            return;
        }

        /** @var array $orderStatuses */
        $orderStatuses = $filters[Constant::ORDER_STATUS];

        $orderStatusQuery = new BoolQuery();

        foreach ($orderStatuses as $orderStatus) {
            $matchStatus = new Term();
            $matchStatus->setTerm('orderStatus', $orderStatus);
            $orderStatusQuery->addShould($matchStatus);
        }

        $this->query->addMust($orderStatusQuery);
    }

    /**
     * @param string $agendaConfirmedStatus
     */
    private function filterByAgendaConfirmedStatus(string $agendaConfirmedStatus)
    {
        $matchAgendaConfirmedStatus = new Term();
        $matchAgendaConfirmedStatus->setTerm('agendaConfirmedStatus', $agendaConfirmedStatus);

        $this->query->addMust($matchAgendaConfirmedStatus);
    }

    /**
     * @param int[]|null $sheets
     */
    private function restrictToSheets(?array $sheetIds)
    {
        if ($sheetIds === null) {
            return;
        }

        $restrictToSheets = new BoolQuery();

        // in case of an empty list, we want to display no result
        if (empty($sheetIds)) {
            $sheetIds[] = 0;
        }

        foreach ($sheetIds as $sheetId) {
            $restrictToSheets->addShould(
                (new Term())->setTerm('id', $sheetId)
            );
        }

        $this->query->addMust($restrictToSheets);
    }

    /**
     * @param Sheet[] $sheetsToExclude
     */
    private function excludeSheets(array $sheetsToExclude)
    {
        if (empty($sheetsToExclude)) {
            return;
        }

        $excludeSheets = new BoolQuery();

        foreach ($sheetsToExclude as $sheetToExclude) {
            $excludeSheets->addShould(
                (new Term())->setTerm('id', $sheetToExclude->getId())
            );
        }

        $this->query->addMustNot($excludeSheets);
    }

    /**
     * @param array $filters
     */
    private function filterByPhoneValidationStatus(array $filters)
    {
        if (isset($filters['phoneValidationStatus'])
            && in_array($filters['phoneValidationStatus'], ValidationStatus::ALL_CONCERNED_STATUS)
        ) {
            $matchPhoneValidationStatus = new Term();
            $matchPhoneValidationStatus->setTerm('phoneValidationStatus', $filters['phoneValidationStatus']);

            $this->query->addMust($matchPhoneValidationStatus);
        }
    }

    /**
     * @param array $filters
     */
    private function filterByAvailabilityConfirmationStatus(array $filters)
    {
        if (isset($filters['availabilityConfirmationStatus'])
            && in_array($filters['availabilityConfirmationStatus'], ConfirmationStatus::ALL_STATUS)
        ) {
            $matchAvailabilityConfirmationStatus = new Term();
            $matchAvailabilityConfirmationStatus->setTerm(
                'availabilityConfirmationStatus',
                $filters['availabilityConfirmationStatus']
            );

            $this->query->addMust($matchAvailabilityConfirmationStatus);
        }
    }

    /**
     * @param array $filters
     */
    private function filterByCommercialStatus(array $filters)
    {
        if (!isset($filters['commercialStatus']) || empty($filters['commercialStatus'])) {
            return;
        }

        $commercialStatuses = $filters['commercialStatus'];

        $commercialStatusQuery = new BoolQuery();

        foreach ($commercialStatuses as $commercialStatus) {
            $matchFollower = new Term();
            $matchFollower->setTerm('commercialStatus', $commercialStatus);
            $commercialStatusQuery->addShould($matchFollower);
        }

        $this->query->addMust($commercialStatusQuery);
    }

    private function filterByTagFilters(array $filters): void
    {
        if (!isset($filters['tagFilters']) || empty($filters['tagFilters'])) {
            return;
        }

        foreach ($filters['tagFilters'] as $tag => $tagFilter) {
            if (empty($tagFilter)) {
                continue;
            }

            $nested = new Nested();
            $nested->setPath('nestedTaggedData');
            $boolQuery = new BoolQuery();

            $tagBoolQuery = new BoolQuery();
            $tagBoolQuery->addMust(new Term([
                'nestedTaggedData.tag' => [
                    'value' => mb_strtolower($tag),
                ],
            ]));

            $tagValuesNestedQuery = new Nested();
            $tagValuesNestedQuery->setPath('nestedTaggedData.values');

            $tagValuesBoolQuery = new BoolQuery();

            foreach ($tagFilter as $tagKey) {
                $tagValuesBoolQuery->addShould(new Term([
                        'nestedTaggedData.values.value' => [
                            'value' => mb_strtolower($tagKey->key),
                        ],
                    ]
                ));
            }

            $tagValuesNestedQuery->setQuery($tagValuesBoolQuery);

            // We check the existence of the tag on the first level of the nested
            $boolQuery->addMust($tagBoolQuery);
            // We then check that the below level contain the given keys
            $boolQuery->addMust($tagValuesNestedQuery);

            $nested->setQuery($boolQuery);
            $this->query->addMust($nested);
        }
    }
}
