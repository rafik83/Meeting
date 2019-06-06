<?php

namespace Proximum\Vimeet\Application\Query\Tip;

use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Tip\Tip;

class IsTipOpened
{
    public function __construct()
    {

    }

    public function isSatisfiedBy(TipTranslationViewQuery $query, TipTranslationView $tipTranslationView): bool
    {
        if (Tip::DISPLAY_DEFAULT === $tipTranslationView->display) {
            return false;
        }

        if (Tip::DISPLAY_ALWAYS_OPENED === $tipTranslationView->display) {
            return true;
        }

        if (Tip::DISPLAY_FIRST_TIME_OPENED === $tipTranslationView->display) {
            // @todo: get / add the tip id in user event extra data

            return true;
        }

        return false;
    }
}
