<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class CardListViewQuery implements \Proximum\Vimeet\Application\Query\Query
{
    /**
     * @var string
     */
    public $locale;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var User
     */
    public $user;

    /**
     * @var bool
     */
    public $editable;

    /**
     * @param Sheet  $sheet
     * @param User   $user
     * @param string $locale
     * @param bool   $editable
     */
    public function __construct(Sheet $sheet, User $user, $locale, $editable = true)
    {
        $this->sheet    = $sheet;
        $this->user     = $user;
        $this->locale   = $locale;
        $this->editable = $editable;
    }
}
