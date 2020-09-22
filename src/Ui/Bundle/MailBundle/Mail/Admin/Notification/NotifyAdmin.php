<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin\Notification;

use Proximum\Vimeet\Application\Components\Mail\AdminMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Admin;

class NotifyAdmin extends AdminMail
{
    public const TEMPLATE = 'MailBundle:Mail:Admin/Notification/notification.html.twig';

    /** @var string */
    protected $subject;

    /** @var string */
    protected $template = self::TEMPLATE;

    /** @var string */
    protected $messageId = 'admin.notify_file_download';

    /** @var bool */
    protected $sendToEmailTeam = true;

    /** @var string */
    private $content;

    /** @var string|null */
    private $link;

    /** @var string|null */
    private $linkTitle;

    public function __construct(
        string $subject,
        string $sender,
        string $receiver,
        string $locale,
        string $content,
        ?string $link = null,
        ?string $linkTitle = null,
        Admin $senderUser = null,
        Admin $receiverUser = null
    ) {
        parent::__construct($sender, $receiver, $locale, $senderUser, $receiverUser);

        if ($link && !$linkTitle) {
            throw new \InvalidArgumentException('linkTitle is required when a link is provided');
        }

        if (!$link && $linkTitle) {
            throw new \InvalidArgumentException('link is required when a linkTitle is provided');
        }

        $this->subject = $subject;
        $this->content = $content;
        $this->link = $link;
        $this->linkTitle = $linkTitle;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getLinkTitle(): ?string
    {
        return $this->linkTitle;
    }
}
