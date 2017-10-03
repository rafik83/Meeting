<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;

class CatalogVisibilityManager
{
    /**
     * @var CatalogVisibilityRepositoryInterface
     */
    private $catalogVisibilityRepository;

    /**
     * CatalogVisibilityManager constructor.
     *
     * @param CatalogVisibilityRepositoryInterface $catalogVisibilityRepository
     */
    public function __construct(CatalogVisibilityRepositoryInterface $catalogVisibilityRepository)
    {
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
    }

    /**
     * @param Event $event
     * @param array $types
     * @param array $categories
     *
     * @return CatalogVisibility
     */
    public function create(Event $event, array $types = [], array $categories = []): CatalogVisibility
    {
        $catalogVisibility = new CatalogVisibility($event);
        $catalogVisibility->updateTypes($types);
        $catalogVisibility->updateCategories($categories);

        $this->catalogVisibilityRepository->add($catalogVisibility);

        return $catalogVisibility;
    }

    /**
     * @param CatalogVisibility $catalogVisibility
     * @param Type              $type
     */
    public function setVisibleType(CatalogVisibility $catalogVisibility, Type $type)
    {
        $catalogVisibility->setType($type);

        $this->catalogVisibilityRepository->set($catalogVisibility);
    }
}
