<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Happening;

interface ProgramElementViewInterface
{
    /**
     * @return bool
     */
    public function isHappeningView(): bool;

    /**
     * @return bool
     */
    public function isMassUnavailabilityView(): bool;
}
