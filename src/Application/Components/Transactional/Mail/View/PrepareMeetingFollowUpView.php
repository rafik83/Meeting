<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\View;

use Proximum\Vimeet\Application\View\Meeting\FollowUpParticipantListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class PrepareMeetingFollowUpView extends AbstractPrepareMail
{
    public string $evaluatingSheetTitle;
    public int $evaluation;
    public FollowUpParticipantListView $metParticipants;

    public function __construct(
        Event $event,
        User $user,
        string $locale,
        Sheet $evaluatedSheet,
        string $evaluatingSheetTitle,
        int $evaluation,
        FollowUpParticipantListView $metParticipants
    ) {
        parent::__construct(
            $event,
            $user,
            Constant::TRANSACTIONAL_MAIL_KEY_MEETING_FOLLOW_UP,
            $locale,
            $evaluatedSheet
        );

        $this->evaluatingSheetTitle = $evaluatingSheetTitle;
        $this->evaluation = $evaluation;
        $this->metParticipants = $metParticipants;
    }
}
