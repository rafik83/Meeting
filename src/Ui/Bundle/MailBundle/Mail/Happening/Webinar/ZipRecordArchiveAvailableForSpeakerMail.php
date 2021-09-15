<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Happening\Webinar;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;

class ZipRecordArchiveAvailableForSpeakerMail extends AbstractMail
{
    public const SUBJECT = 'mail.happening.webinar.zip_record_archive_available_for_speaker.subject';
    public const TEMPLATE = 'MailBundle:Mail:Happening/zip_record_archive_available_for_speaker.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

    /** @var string */
    protected $messageId = 'happening.webinar.speaker.record_file_available';

    /** @var Happening */
    public $happening;

    /** @var Event */
    public $event;

    public function __construct(
        Happening $happening,
        Event $event,
        $sender,
        $receiver,
        $locale
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
        $this->happening = $happening;
    }

    public function getWebinarRecordZipFileUrl(): ?string
    {
        return $this->happening->getWebinarRecordZipFileUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectParameters(): array
    {
        return [
            '%happeningTitle%' => $this->getHappeningTitle(),
            '%eventTitle%' => $this->event->getTitle(),
        ];
    }

    public function getHappeningTitle(): string
    {
        return $this->happening->getTitle(
            $this->event->getAvailableLocale($this->locale)
        );
    }
}
