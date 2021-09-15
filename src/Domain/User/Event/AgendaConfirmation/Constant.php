<?php

namespace Proximum\Vimeet\Domain\User\Event\AgendaConfirmation;

use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AgendaConfirmationStatusView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AgendaConfirmedView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AgendaNotConfirmedView;

final class Constant
{
    const AGENDA_CONFIRMED     = 'confirmed';
    const AGENDA_NOT_CONFIRMED = 'not_confirmed';

    const AGENDA_CONFIRMATION_STATUS = [
        self::AGENDA_NOT_CONFIRMED,
        self::AGENDA_CONFIRMED,
    ];

    /**
     * @param AgendaConfirmationStatusView $view
     *
     * @return null|string
     */
    public static function getStatusFromView(AgendaConfirmationStatusView $view): ?string
    {
        if ($view instanceof AgendaConfirmedView) {
            return self::AGENDA_CONFIRMED;
        }

        if ($view instanceof AgendaNotConfirmedView) {
            return self::AGENDA_NOT_CONFIRMED;
        }

        return null;
    }
}
