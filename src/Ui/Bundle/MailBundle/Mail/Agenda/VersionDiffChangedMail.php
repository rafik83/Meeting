<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Agenda;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class VersionDiffChangedMail extends UserMail
{
    public const SUBJECT = 'mail.agenda.version_diff_changed.subject';
    public const TEMPLATE = 'MailBundle:Mail:Agenda/versionDiffChanged.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:Agenda/versionDiffChanged_full_text.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

    /** @var string */
    protected $messageId = 'agenda.version_diff_changed';

    /** @var User */
    private $user;

    /** @var string */
    protected $firstname;

    /** @var string */
    protected $lastname;

    /** @var string */
    private $agendaModifications;

    /** @var Sheet */
    private $sheet;

    public function __construct(
        Event $event,
        User $user,
        Sheet $sheet,
        string $agendaModifications,
        string $sender,
        string $receiver,
        string $locale,
        ParticipantInfoView $participantInfoView
    ) {
        parent::__construct($sender, $receiver, $locale, $event, $participantInfoView);

        $this->sheet = $sheet;
        $this->user = $user;
        $this->agendaModifications = $agendaModifications;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    public function getAgendaModifications(): string
    {
        return $this->agendaModifications;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectParameters()
    {
        return [
            '%event%' => $this->getEvent()->getTitle(),
        ];
    }
}
