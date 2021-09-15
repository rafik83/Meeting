<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\View;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class PrepareVersionDiffChangedMailView extends AbstractPrepareMail
{
    /** @var string */
    private $agendaModifications;

    public function __construct(
        Event $event,
        User $user,
        Sheet $sheet,
        string $locale,
        string $agendaModifications
    ) {
        parent::__construct(
            $event,
            $user,
            Constant::TRANSACTIONAL_MAIL_KEY_AGENDA_VERSION_DIFF_CHANGED,
            $locale,
            $sheet
        );

        $this->agendaModifications = $agendaModifications;
    }

    public function getAgendaModifications(): string
    {
        return $this->agendaModifications;
    }
}
