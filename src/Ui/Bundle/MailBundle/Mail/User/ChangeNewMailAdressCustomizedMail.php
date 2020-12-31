<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\User;

use Proximum\Vimeet\Application\Components\Mail\AbstractCustomizedMail;
use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\Event;

class ChangeNewMailAdressCustomizedMail extends AbstractCustomizedMail
{
    public const TEMPLATE = 'MailBundle:Mail:base.html.twig';

    /** @var string */
    protected $template = self::TEMPLATE;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @var string
     */
    protected $token;

    public function __construct(
        Event $event,
        string $sender,
        string $receiver,
        string $locale,
        string $token,
        string $subject,
        string $content
    ) {
        parent::__construct($event, $sender, $receiver, $locale);

        $this->token = $token;
        $this->subject = $subject;
        $this->content = $content;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
