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
use Proximum\Vimeet\Domain\Model\Filter\TaggedNomenclatureFilter;

interface TaggedNomenclatureFilterRepositoryInterface
{
    /**
     * @param Event $event
     */
    public function deleteForEvent(Event $event);

    /**
     * @param TaggedNomenclatureFilter $taggedNomenclatureFilter
     */
    public function add(TaggedNomenclatureFilter $taggedNomenclatureFilter);

    /**
     * @param Event  $event
     * @param string $tag
     *
     * @return TaggedNomenclatureFilter|null
     */
    public function getByEventAndTag(Event $event, $tag);
}
