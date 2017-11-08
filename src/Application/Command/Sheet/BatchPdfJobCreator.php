<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;

class BatchPdfJobCreator extends AbstractBatch
{
    /** @var string */
    public $locale;

    /** @var string */
    public $emailToNotify;

    /** @var array */
    public $sheetIds;

    /**
     * @param array  $sheetIds
     * @param Admin  $admin
     * @param string $locale
     */
    public function __construct(array $sheetIds, Admin $admin, string $locale)
    {
        $this->emailToNotify = $admin->getEmail();
        $this->locale        = $locale;
        $this->sheetIds      = $sheetIds;
    }
}
