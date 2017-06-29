<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet\Group;

class BatchAssignToGroup extends AbstractBatch
{
    /** @var Group|null */
    public $group;

    /** @var string */
    public $locale;

    /**
     * BatchAssignToGroup constructor.
     *
     * @param array      $ids
     * @param Group|null $group
     * @param string     $locale
     */
    public function __construct(array $ids, Group $group = null, $locale)
    {
        $this->ids    = $ids;
        $this->group  = $group;
        $this->locale = $locale;
    }
}
