<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareMeetingFollowUpView;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Meeting\MeetingFollowUpMail;

class MetParticipantsSubstitution implements SubstituteInterface
{
    private TemplatingAdapterInterface $templating;

    public function __construct(TemplatingAdapterInterface $templating)
    {
        $this->templating = $templating;
    }

    public function substitute(AbstractPrepareMail $prepareMail): string
    {
        if (!$prepareMail instanceof PrepareMeetingFollowUpView) {
            return '';
        }

        return $this->templating->render(MeetingFollowUpMail::TEMPLATE_MET_PARTICIPANTS, [
            'metParticipantViews' => $prepareMail->metParticipants->participantViews,
            'evaluatedSheetId' => $prepareMail->sheet->getId(),
            'showEmail' => $prepareMail->showEmail,
            'showPhone' => $prepareMail->showPhone,
        ]);
    }
}
