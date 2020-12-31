<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetsDuplicatedMail extends AbstractMail
{
    /** @var string */
    protected $subject = 'mail.event.sheets_duplicated.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail:Event/sheetsDuplicated.html.twig';

    /** @var string */
    protected $messageId = Events::EVENT_SHEETS_DUPLICATED;

    /** @var Event */
    public $event;

    /** @var Event */
    public $originEvent;

    /** @var Sheet[] */
    public $importedSheets;

    /** @var string[] array of emails */
    public $userAlreadyGroupManagerOnSameEvent;

    /** @var string[] array of emails */
    public $userAlreadyParticipantOrOwnerOnGroupOnSameEvent;

    /**
     * @param Event    $event
     * @param Event    $originEvent
     * @param array    $importedSheets
     * @param string[] $userAlreadyGroupManagerOnSameEvent              array of emails
     * @param string[] $userAlreadyParticipantOrOwnerOnGroupOnSameEvent array of emails
     * @param string   $sender
     * @param string   $receiver
     * @param string   $locale
     */
    public function __construct(
        Event $event,
        Event $originEvent,
        array $importedSheets,
        array $userAlreadyGroupManagerOnSameEvent,
        array $userAlreadyParticipantOrOwnerOnGroupOnSameEvent,
        $sender,
        $receiver,
        $locale
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
        $this->originEvent = $originEvent;
        $this->importedSheets = $importedSheets;
        $this->userAlreadyGroupManagerOnSameEvent = $userAlreadyGroupManagerOnSameEvent;
        $this->userAlreadyParticipantOrOwnerOnGroupOnSameEvent = $userAlreadyParticipantOrOwnerOnGroupOnSameEvent;
    }

    public function getSubjectParameters(): array
    {
        return [
            '%event%' => $this->event->getTitle(),
            '%eventOrigin%' => $this->originEvent->getTitle(),
        ];
    }
}
