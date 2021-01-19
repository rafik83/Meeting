<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\AbstractCustomizedMail;
use Proximum\Vimeet\Domain\Model\Event;

class ChangeOldMailAdressCustomizedMail extends AbstractCustomizedMail
{
    public const TEMPLATE = 'MailBundle:Mail:base.html.twig';

    /** @var string */
    protected $template = self::TEMPLATE;

    /**
     * @var string
     */
    private $newMail;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    public function __construct(
        Event $event,
        string $sender,
        string $receiver,
        string $locale,
        string $newMail,
        string $subject,
        string $content
    ) {
        parent::__construct($event, $sender, $receiver, $locale);

        $this->newMail = $newMail;
        $this->subject = $subject;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getNewMail()
    {
        return $this->newMail;
    }
}
