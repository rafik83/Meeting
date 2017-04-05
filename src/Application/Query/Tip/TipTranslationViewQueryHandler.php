<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Tip;

use Proximum\Vimeet\Application\Exception\Tip\TipTranslationNotFoundException;
use Proximum\Vimeet\Application\View\Tip\TipTranslationView;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipTranslationViewQueryHandler
{
    const PATH_CATALOG            = 'event_catalog_index';
    const PATH_MEETING_MANAGEMENT = 'event_meeting_list_request';
    const PATH_PRINT_PLANNING     = '';

    /**
     * Constant that define TipRepositoryInterface methods to be called
     */
    const METHOD_PLANNING           = 'findForPlanning';
    const METHOD_MEETING_MANAGEMENT = 'findForMeetingManagement';
    const METHOD_CATALOG            = 'findForCatalog';

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
     * @return TipTranslationView
     *
     * @throws TipTranslationNotFoundException
     */
    public function handle(TipTranslationViewQuery $query)
    {
        $repositoryMethod = $this->getRepositoryMethod($query);
        $tip = $this->tipRepository->$repositoryMethod($query->path);

        if ($tip->getTranslations()->get($query->locale) === null) {
            throw new TipTranslationNotFoundException();
        }

        $tipTranslation = $tip->getTranslations()->get($query->locale);

        return new TipTranslationView($tipTranslation->getTitle(), $tipTranslation->getContent());
    }

    private function getRepositoryMethod(TipTranslationViewQuery $query)
    {
        if ($query->path === self::PATH_CATALOG) {
            return 'findForCatalog';
        }

        if ($query->path === self::PATH_MEETING_MANAGEMENT) {
            return 'findForMeetingManagement';
        }

        if ($query->path === self::PATH_PRINT_PLANNING) {
            return 'findForPlanning';
        }
    }

}