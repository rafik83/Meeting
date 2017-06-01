<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Tip\Event;

use Proximum\Vimeet\Application\Exception\Tip\NoTipAvailableException;
use Proximum\Vimeet\Application\View\Tip\TipTranslationView;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipListViewQueryHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /**
     * TipListViewQueryHandler constructor.
     *
     * @param TipRepositoryInterface $tipRepository
     */
    public function __construct(TipRepositoryInterface $tipRepository)
    {
        $this->tipRepository = $tipRepository;
    }

    /**
     * @param TipListViewQuery $query
     *
     * @return TipTranslationView[]
     *
     * @throws NoTipAvailableException
     */
    public function handle(TipListViewQuery $query)
    {
        $tipTranslationViews = $this->tipRepository->getTipTranslationViewByLocale($query->locale);

        if (empty($tipTranslationViews)) {
            throw new NoTipAvailableException();
        }

        return $tipTranslationViews;
    }
}
