<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Sheet;

final class Constant
{
    const CREATED_TODAY     = 'created_today';
    const CREATED_THIS_WEEK = 'created_this_week';
    const NO_ORDER          = 'no_order';
    const HAS_CART          = 'has_cart';

    const ORDER_BY_ALPHABETICAL          = 'alphabetical';
    const ORDER_BY_DATE_ADDED_TO_CATALOG = 'dateAddedToCatalog';
    const ORDER_BY_CREATED_AT            = 'created_at';
    const ORDER_BY_RELEVANCE             = 'relevance';
}
