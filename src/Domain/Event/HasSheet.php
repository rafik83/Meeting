<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class HasSheet
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * HasSheet constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function on(Event $event)
    {
        return $this->sheetRepository->countByEvent($event) > 0;
    }
}
