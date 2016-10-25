<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\View\Catalog\KeywordView;

class KeywordViewQueryHandler
{
    /**
     * @var SheetSearchAdapterInterface
     */
    private $sheetSearchAdapter;

    /**
     * LocalizationViewQueryHandler constructor.
     *
     * @param SheetSearchAdapterInterface $sheetSearchAdapter
     */
    public function __construct(SheetSearchAdapterInterface $sheetSearchAdapter)
    {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
    }

    /**
     * @param KeywordViewQuery $query
     *
     * @return KeywordView[]
     */
    public function handle(KeywordViewQuery $query)
    {
        $keywords = $this->sheetSearchAdapter->findKeyword(
            $query->event,
            $query->filter,
            $query->locale
        );

        $keywordViews = [];

        // handle keyword
        if (!empty($keywords['keywords']['keywords_aggs']['keywords_filter']['keyword'])) {
            foreach ($keywords['keywords']['keywords_aggs']['keywords_filter']['keyword']['buckets'] as $keyword) {
                $keywordViews[] = new KeywordView($keyword['key']);
            }
        }

        return $keywordViews;
    }
}
