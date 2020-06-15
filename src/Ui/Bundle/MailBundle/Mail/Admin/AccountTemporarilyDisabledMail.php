<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin;

use Proximum\Vimeet\Application\Components\Mail\AdminMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\User;

class AccountTemporarilyDisabledMail extends AdminMail
{
    public const SUBJECT = 'mail.userAccountTemporarilyDisabled.subject';
    public const TEMPLATE = 'MailBundle:Mail:Admin/accountTemporarilyDisabled.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

    /** @var string */
    protected $messageId = Events::ADMIN_ACCOUNT_TEMPORARILY_DISABLED;

    /** @var bool */
    protected $sendToEmailTeam = true;

    public function __construct(
        $sender,
        $receiver,
        $locale,
        User $receiverUser = null
    ) {
        parent::__construct($sender, $receiver, $locale, null, $receiverUser);
    }
}
