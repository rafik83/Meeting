<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SubmitValidation
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var User
     */
    public $user;

    /**
     * SubmitValidation constructor.
     *
     * @param Sheet  $sheet
     * @param User   $user
     * @param string $locale
     */
    public function __construct(Sheet $sheet, User $user, $locale)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
        $this->user   = $user;
    }
}
