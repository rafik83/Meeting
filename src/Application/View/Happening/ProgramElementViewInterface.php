<?php

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
