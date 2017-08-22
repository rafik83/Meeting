<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\External;

use Proximum\Vimeet\Application\View\CatalogVisibility\MessageView;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;

class CatalogVisibilityMessageQueryHandler
{
    /**
     * @var CatalogVisibilityRepositoryInterface
     */
    private $catalogVisibilityRepository;

    /**
     * CatalogVisibilityMessageQueryHandler constructor.
     *
     * @param CatalogVisibilityRepositoryInterface $catalogVisibilityRepository
     */
    public function __construct(CatalogVisibilityRepositoryInterface $catalogVisibilityRepository)
    {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
    }

    /**
     * @param CatalogVisibilityMessageQuery $query
     *
     * @return null|MessageView
     */
    public function handle(CatalogVisibilityMessageQuery $query): ?MessageView
    {
        $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($query->event);

        if ($catalogVisibility === null || $catalogVisibility->hasMessage() === false) {
            return null;
        }

        $message = $catalogVisibility->getMessage($query->locale);

        if ($message === null) {
            return null;
        }

        return new MessageView($message->getTitle(), $message->getContent());
    }
}
