<?php

namespace Proximum\Vimeet\Application\Adapter;

use Elastica\Result;
use Proximum\Vimeet\Application\Query\Messaging\Campaign\SheetListView;
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Application\View\Participant\ParticipantsSheetIdsView;
use Proximum\Vimeet\Application\View\Sheet\SheetIdsView;
use Proximum\Vimeet\Domain\ConditionRules\View\RuleInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;

interface SheetSearchAdapterInterface
{
    public const ES_FIELD_TYPE                  = 'type';
    public const ES_FIELD_CATEGORIES            = 'categories.id';
    public const ES_FIELD_ORGANIZATION_CATEGORY = 'organizationCategory';
    public const ES_FIELD_IN_CATALOG            = 'inCatalog';
    public const ES_FIELD_POSITION              = 'position';

    /** ElasticSearch keys */
    public const ES_BUCKETS   = 'buckets';
    public const ES_DOC_COUNT = 'doc_count';
    public const ES_KEY       = 'key';

    public const ES_PATH_POSITION   = 'participants';
    public const ES_PATH_CATEGORIES = 'categories';

    /**
     * @param Event               $event
     * @param array               $filters
     * @param string|null         $orderBy
     * @param string              $locale
     * @param array               $nomenclatureItems
     * @param AvailableSlotView[] $availableSlotIds
     * @param Sheet[]             $sheetsToExclude
     *
     * @return Result[]
     */
    public function find(
        Event $event,
        array $filters,
        string $orderBy = null,
        string $locale,
        array $nomenclatureItems = [],
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ): array;

    public function paginate(
        Event $event,
        array $filters,
        string $orderBy = null,
        int $page,
        int $limit,
        string $locale,
        bool $getAggregations,
        array $nomenclatureItems = [],
        array $availableSlotIds = [],
        array $sheetsToExclude = [],
        ?RuleInterface $condition = null
    ): PaginatedResult;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     * @param null|RuleInterface $condition
     *
     * @return int[]
     */
    public function getSheetIds(Event $event, array $filters, string $locale, ?RuleInterface $condition = null): array;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     * @param null|RuleInterface $condition
     *
     * @return SheetListView[]
     */
    public function getSheetListView(Event $event, array $filters, string $locale, ?RuleInterface $condition = null): array;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     * @param null|RuleInterface $condition
     *
     * @return SheetIdsView
     */
    public function getSheetIdsView(Event $event, array $filters, string $locale, ?RuleInterface $condition = null): SheetIdsView;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     *
     * @return ParticipantsSheetIdsView
     */
    public function getParticipantsSheetIdsView(Event $event, array $filters, string $locale): ParticipantsSheetIdsView;

    /**
     * @param Event  $event
     * @param string $filter
     * @param array  $defaultFilters
     * @param string $locale
     *
     * @return array
     */
    public function findLocalization(Event $event, string $filter, array $defaultFilters, string $locale): array;

    /**
     * @param Event  $event
     * @param string $filter
     * @param array  $defaultFilters
     * @param string $locale
     *
     * @return array
     */
    public function findKeyword(Event $event, string $filter, array $defaultFilters, string $locale): array;

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param string $filterToRemove
     * @param array  $nomenclatureItems
     * @param array  $availableSlotIds
     * @param array  $sheetsToExclude
     *
     * @return array
     */
    public function getTypeAggregations(
        Event $event,
        string $locale,
        array $filters,
        string $filterToRemove,
        array $nomenclatureItems = [],
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ): array;

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param string $filterToRemove
     * @param array  $nomenclatureItems
     * @param array  $availableSlotIds
     * @param array  $sheetsToExclude
     *
     * @return array
     */
    public function getCategoryAggregations(
        Event $event,
        string $locale,
        array $filters,
        string $filterToRemove,
        array $nomenclatureItems = [],
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ): array;

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param string $filterToRemove
     *
     * @return array
     */
    public function getOrganizationCategoryAggregations(
        Event $event,
        string $locale,
        array $filters,
        string $filterToRemove
    ): array;

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param string $filterToRemove
     *
     * @return array
     */
    public function getPositionAggregations(
        Event $event,
        string $locale,
        array $filters,
        string $filterToRemove
    ): array;

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return array of aggregate with the formats:
     *               [
     *               "countryCodes" => [
     *               "doc_count_error_upper_bound" => 0,
     *               "sum_other_doc_count" => 0,
     *               "buckets" => [
     *               [
     *               "key" => "fr",
     *               "doc_count" => 186
     *               ],
     *               [
     *               "key" => "gb",
     *               "doc_count" => 29
     *               ]
     *               ]
     *               ]
     *               ]
     */
    public function getCountries(Event $event, string $locale): array;
}
