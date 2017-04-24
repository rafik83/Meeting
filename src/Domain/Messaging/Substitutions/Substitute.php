<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Model\Sheet;

interface Substitute
{
    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return string
     */
    public function getValue(Sheet $sheet, $locale);
}
