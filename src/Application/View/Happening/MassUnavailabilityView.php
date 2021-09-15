<?php

namespace Proximum\Vimeet\Application\View\Happening;

use Proximum\Vimeet\Application\View\Unavailability\Mass\AbstractMassView;

class MassUnavailabilityView extends AbstractMassView implements ProgramElementViewInterface
{
    /**
     * {@inheritdoc}
     */
    public function isHappeningView(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isMassUnavailabilityView(): bool
    {
        return true;
    }
}
