<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;

class CatalogAccessChecker extends AccessChecker
{
    /**
     * @var CatalogVisibilityRepositoryInterface
     */
    private $catalogVisibilityRepository;

    /**
     * CatalogAccessChecker constructor.
     *
     * @param CatalogVisibilityRepositoryInterface $catalogVisibilityRepository
     * @param \DateTimeInterface                   $datetime
     */
    public function __construct(
        CatalogVisibilityRepositoryInterface $catalogVisibilityRepository,
        \DateTimeInterface $datetime
    ) {
        parent::__construct($datetime);
        $this->catalogVisibilityRepository = $catalogVisibilityRepository;
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function allowedToAccess(Event $event)
    {
        if (null === $event->getConfiguration()->getCatalogOnlineDate()) {
            return false;
        }

        return $this->datetime >= $event->getConfiguration()->getCatalogOnlineDate();
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function allowedToAccessExternal(Event $event)
    {
        $catalogVisibility = $this->catalogVisibilityRepository->getByEvent($event);

        return $catalogVisibility !== null;
    }
}
