<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Tip;

use Proximum\Vimeet\Application\View\Tip\TipTranslationView;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipTranslationViewQueryHandler
{
    const CONTEXT_CATALOG            = 'event_catalog_index';
    const CONTEXT_MEETING_MANAGEMENT = 'event_meeting_list_request';
    const CONTEXT_PRINT_PLANNING     = 'print_planning';

    /**
     * keys are context, value are table fields
     *
     * @var array
     */
    private static $contextsMapping = [
        self::CONTEXT_CATALOG            => 'onCatalog',
        self::CONTEXT_MEETING_MANAGEMENT => 'onMeetingManagement',
        self::CONTEXT_PRINT_PLANNING     => 'onPrintPlanning',
    ];

    /** @var TipRepositoryInterface */
    private $tipRepository;

    /**
     * TipTranslationViewQueryHandler constructor.
     *
     * @param TipRepositoryInterface $tipRepository
     */
    public function __construct(TipRepositoryInterface $tipRepository)
    {
        $this->tipRepository = $tipRepository;
    }

    /**
     * @param TipTranslationViewQuery $query
     *
     * @return TipTranslationView[]
     */
    public function handle(TipTranslationViewQuery $query)
    {
        if (!isset(self::$contextsMapping[$query->context])) {
            return null;
        }

        $tipTranslationViews = $this->tipRepository->getByContext(self::$contextsMapping[$query->context], $query->locale);

        if (empty($tipTranslationViews)) {
            return null;
        }

        $tipTranslationListView = [];

        foreach ($tipTranslationViews as $tipTranslationView) {
            $tipTranslationListView[] = $tipTranslationView;
        }

        return $tipTranslationListView;
    }
}
