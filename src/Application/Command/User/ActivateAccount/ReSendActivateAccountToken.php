<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\ActivateAccount;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ReSendActivateAccountToken
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var string */
    public $locale;

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
