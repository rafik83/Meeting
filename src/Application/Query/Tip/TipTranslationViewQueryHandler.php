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
    /**
     * keys are context, value are table fields
     *
     * @var array
     */
    private static $contextsMapping = [
        'event_catalog_index'        => 'onCatalog',
        'event_meeting_list_request' => 'onMeetingManagement',
        'print_planning'             => 'onPrintPlanning',
    ];

    /** @var TipRepositoryInterface */
    private $tipRepository;

    /** @var array */
    private $tipTranslationListView = [];

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
        if(!isset(self::$contextsMapping[$query->context])) {
            return null;
        }

        $tipTranslations = $this->tipRepository->getByContext(self::$contextsMapping[$query->context], $query->locale);

        if (!$tipTranslations) {
            return null;
        }

        foreach ($tipTranslations as $translation) {
            $this->tipTranslationListView[] = $translation;
        }

        return $this->tipTranslationListView;
    }
}
