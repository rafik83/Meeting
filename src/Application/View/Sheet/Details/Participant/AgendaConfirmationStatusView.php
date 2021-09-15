<?php

namespace Proximum\Vimeet\Application\View\Sheet\Details\Participant;

abstract class AgendaConfirmationStatusView
{
    const TRANS_KEY = 'admin.sheet.details.participant.';

    /**
     * Not asked are considered as not validated
     *
     * @var string
     */
    public $message = self::TRANS_KEY . 'not_concerned';

    /** @var string */
    public $indicator = 'danger';

    /**
     * @return bool
     */
    public function hasExtraMessage(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getExtraMessage(): string
    {
        return self::TRANS_KEY . '.tooltip.nothing';
    }

    /**
     * @return bool
     */
    public function canBeEdited(): bool
    {
        return true;
    }
}
