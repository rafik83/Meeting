<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

class AgendaConfirmationNotConcernedView extends AgendaConfirmationStatusView
{
    /** @var string */
    public $message = self::TRANS_KEY . 'agenda_confirmation_not_concerned';

    /** @var string */
    public $indicator = 'info';

    /**
     * {@inheritdoc}
     */
    public function hasExtraMessage(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraMessage(): string
    {
        return self::TRANS_KEY . 'tooltip.not_concerned';
    }

    /**
     * {@inheritdoc}
     */
    public function canBeEdited(): bool
    {
        return false;
    }
}
