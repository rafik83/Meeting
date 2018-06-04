<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Filter;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\FilledTemplateFilter;

interface FilledTemplateFilterRepositoryInterface
{
    public function getByEvent(Event $event): ?array;

    public function deleteForEvent(Event $event): void;

    public function add(FilledTemplateFilter $booleanTemplateFilter): void;
}
