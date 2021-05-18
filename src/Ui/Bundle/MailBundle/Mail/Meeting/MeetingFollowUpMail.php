<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Meeting;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Meeting\FollowUpParticipantListView;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;

class MeetingFollowUpMail extends UserMail
{
    public const SUBJECT = 'mail.meeting.followUp.subject';
    public const TEMPLATE = 'MailBundle:Mail:Meeting/followUp.html.twig';
    public const TEMPLATE_MET_PARTICIPANTS = 'MailBundle:Mail:Meeting/followUpMetParticipants.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:Meeting/followUp_full_text.html.twig';

    /** {@inheritdoc} */
    protected $subject = self::SUBJECT;
    /** {@inheritdoc} */
    protected $template = self::TEMPLATE;
    /** {@inheritdoc} */
    protected $messageId = Events::MEETING_EVALUATION_UPDATE_EXPIRED;
    /** {@inheritdoc} */
    protected $sendToEmailTeam = false;

    private int $evaluatedSheetId;
    private string $evaluatedSheetTitle;
    private string $evaluatingSheetTitle;
    private int $meetingEvaluation;
    private FollowUpParticipantListView $metParticipants;

    public function __construct(
        Event $event,
        string $sender,
        string $receiver,
        string $locale,
        ParticipantInfoView $participantInfoView,
        int $evaluatedSheetId,
        string $evaluatedSheetTitle,
        string $evaluatingSheetTitle,
        int $meetingEvaluation,
        FollowUpParticipantListView $metParticipants
    ) {
        parent::__construct($sender, $receiver, $locale, $event, $participantInfoView);

        $this->evaluatedSheetId = $evaluatedSheetId;
        $this->evaluatedSheetTitle = $evaluatedSheetTitle;
        $this->evaluatingSheetTitle = $evaluatingSheetTitle;
        $this->meetingEvaluation = $meetingEvaluation;
        $this->metParticipants = $metParticipants;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectParameters()
    {
        return [
            '%event%' => $this->getEvent()->getTitle(),
            '%evaluatingSheet%' => $this->getEvaluatingSheetTitle(),
            '%evaluatedSheet%' => $this->getEvaluatedSheetTitle(),
        ];
    }

    public function getEvaluatedSheetId(): int
    {
        return $this->evaluatedSheetId;
    }

    public function getEvaluatedSheetTitle(): string
    {
        return $this->evaluatedSheetTitle;
    }

    public function getEvaluatingSheetTitle(): string
    {
        return $this->evaluatingSheetTitle;
    }

    public function getMeetingEvaluation(): int
    {
        return $this->meetingEvaluation;
    }

    public function getMetParticipantViews(): array
    {
        return $this->metParticipants->participantViews;
    }
}
