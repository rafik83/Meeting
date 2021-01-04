<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Happening;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\Happening\HappeningParticipationView;

class HappeningParticipationAutomaticallyUpdatedMail extends AbstractMail
{
    public const SUBJECT = 'mail.happening.participation.subject';
    public const TEMPLATE = 'MailBundle:Mail:Happening/participation.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:Happening/participation_full_text.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

    /** @var string */
    protected $messageId = Events::HAPPENING_PARTICIPATION_AUTOMATICALLY_UPDATED;

    /** @var HappeningParticipationView[] */
    public $happeningParticipationViews;

    /** @var Event */
    public $event;

    public function __construct(
        array $happeningParticipationViews,
        Event $event,
        $sender,
        $receiver,
        $locale
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
        $this->happeningParticipationViews = $happeningParticipationViews;
    }
}
