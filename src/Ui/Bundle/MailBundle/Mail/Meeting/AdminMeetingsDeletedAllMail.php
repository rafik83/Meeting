<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Meeting;

use Proximum\Vimeet\Application\Components\Mail\AdminMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class AdminMeetingsDeletedAllMail extends AdminMail
{
    /** @var string */
    protected $subject = 'admin.mail.meetings.deletedAll.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail:Meeting/meetingsDeletedAll.html.twig';

    /** @var string */
    protected $messageId = Events::ADMIN_MEETINGS_DELETED_ALL;

    /** @var bool */
    protected $sendToEmailTeam = false;

    /** @var Admin */
    protected $admin;

    public function __construct($sender, $receiver, $locale, Admin $admin, Event $event)
    {
        parent::__construct($sender, $receiver, $locale);

        $this->admin = $admin;
        $this->event = $event;
    }

    public function getAdmin(): Admin
    {
        return $this->admin;
    }
}
