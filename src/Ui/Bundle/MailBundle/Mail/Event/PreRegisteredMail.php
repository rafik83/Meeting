<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Event;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class PreRegisteredMail extends UserMail
{
    public const SUBJECT = 'mail.event.preregister.subject';
    public const TEMPLATE = 'MailBundle:Mail:Event/preregister.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:Event/preregister_full_text.html.twig';

    /**
     * @var string
     */
    protected $subject = self::SUBJECT;

    /**
     * @var string
     */
    protected $template = self::TEMPLATE;

    /**
     * @var string
     */
    protected $messageId = Events::EVENT_PRE_REGISTERED;

    /**
     * @var Participant
     */
    private $participant;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = true;

    /**
     * @param Participant         $participant
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param ParticipantInfoView $participantInfoView
     */
    public function __construct(
        Participant $participant,
        $sender,
        $receiver,
        $locale,
        ParticipantInfoView $participantInfoView
    ) {
        parent::__construct(
            $sender,
            $receiver,
            $locale,
            $participant->getSheet()->getEvent(),
            $participantInfoView
        );

        $this->sheet       = $participant->getSheet();
        $this->participant = $participant;
    }

    /**
     * @return Participant
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->sheet->getType()->getTitle($this->locale);
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
