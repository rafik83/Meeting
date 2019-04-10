<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\BadgeLink;

class LeniBadgeLinkParametersView
{
    /** @var string */
    public $link;

    /** @var int[] */
    public $concernedTypeIds;

    public function __construct(string $link, $concernedTypeIds)
    {
        $this->link = $link;
        $this->concernedTypeIds = $concernedTypeIds;
    }
}
