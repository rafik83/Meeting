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
use Proximum\Vimeet\Application\View\Tip\Event\TipView;
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
     * @return TipView[]
     *
     * @throws NoTipAvailableException
     */
    public function handle(TipListViewQuery $query)
    {
        $tips = $this->tipRepository->getAll();

        if (empty($tips)) {
            throw new NoTipAvailableException();
        }

        $tipViews = [];

        foreach ($tips as $tip) {
            $tipViews[] = new TipView($tip->getId(), $tip->getTitle(), $query->locale);
        }

        return $tipViews;
    }
}
