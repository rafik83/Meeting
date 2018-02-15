<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planning;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Type;

class ExportPlanningJobCreator
{
    /** @var Type[] */
    public $types;

    /** @var string */
    public $orderBy;

    /** @var string */
    public $locale;

    /** @var string */
    public $emailToNotify;

    /**
     * Create the job for the planning export
     *
     * @param Admin  $admin
     * @param string $locale
     */
    public function __construct(Admin $admin, $locale)
    {
        $this->types         = [];
        $this->emailToNotify = $admin->getEmail();
        $this->locale        = $locale;
    }
}
