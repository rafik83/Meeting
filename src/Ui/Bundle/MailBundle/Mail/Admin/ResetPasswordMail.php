<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin;

use Proximum\Vimeet\Application\Components\Mail\AdminMail;
use Proximum\Vimeet\Application\Event\Events;

class ResetPasswordMail extends AdminMail
{
    /**
     * @var string
     */
    protected $subject = 'admin.mail.resetPassword.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:Admin/resetPassword.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::ADMIN_PASSWORD_RESET;

    /**
     * @var string
     */
    private $token;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = false;

    /**
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     * @param string $token
     */
    public function __construct($sender, $receiver, $locale, $token)
    {
        parent::__construct($sender, $receiver, $locale);
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
