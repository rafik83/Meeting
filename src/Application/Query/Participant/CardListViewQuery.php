<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class CardListViewQuery
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
     * @param Sheet  $sheet
     * @param User   $user
     * @param string $locale
     */
    public function __construct(Sheet $sheet, User $user, $locale)
    {
        $this->sheet  = $sheet;
        $this->user   = $user;
        $this->locale = $locale;
    }
}
