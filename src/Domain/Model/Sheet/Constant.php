<?php

namespace Proximum\Vimeet\Domain\Model\Sheet;

final class Constant
{
    const CREATED_TODAY        = 'created_today';
    const CREATED_THIS_WEEK    = 'created_this_week';
    const NO_ORDER             = 'no_order';
    const HAS_CART             = 'has_cart';
    const HAS_REMAINING_TO_PAY = 'hasRemainingToPay';
    const BOOLEAN_FILTER       = 'boolean_filters';
    const FILLED_FILTER        = 'filled_filters';

    const ORDER_BY_ALPHABETICAL          = 'alphabetical';
    const ORDER_BY_COMPLETENESS          = 'completeness';
    const ORDER_BY_DATE_ADDED_TO_CATALOG = 'dateAddedToCatalog';
    const ORDER_BY_CREATED_AT            = 'created_at';
    const ORDER_BY_RELEVANCE             = 'relevance';

    const IMPORTED                    = 'imported';
    const IMPORTED_WITHOUT_CONNECTION = 'imported_without_connection';
    const IMPORTED_WITH_CONNECTION    = 'imported_with_connection';
    const NOT_IMPORTED                = 'not_imported';

    const FILTER_IMPORTED = 'imported';

    const ORDER_STATUS                           = 'order_status';
    const ORDER_STATUS_NO_ORDER                  = 'no_order';
    const ORDER_STATUS_TOTAL_ORDER_EQUAL_ZERO    = 'total_order_equal_zero';
    const ORDER_STATUS_TOTAL_ORDER_SUPERIOR_ZERO = 'total_order_superior_zero';
}
