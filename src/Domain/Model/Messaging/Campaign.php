<?php

namespace Proximum\Vimeet\Domain\Model\Messaging;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Campaign
{
    public const RECIPIENT_SHEET_OWNER = 'sheet_owner';
    public const RECIPIENT_PARTICIPANTS = 'participants';
    public const RECIPIENT_BILLING_CONTACT = 'billing_contact';
    public const RECIPIENT_USER = 'user';

    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var string */
    private $title;

    /** @var array */
    private $filters;

    /** @var ArrayCollection|Sheet[] */
    private $sheets;

    /** @var ArrayCollection|User[] */
    private $users;

    /** @var Message|null */
    private $message;

    /**
     * @var string[]
     *
     * @see self::getRecipientChoices
     */
    private $recipients;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var \DateTimeInterface|null */
    private $sentAt;

    /** @var \DateTimeInterface|null */
    private $processedAt;

    public function __construct(Event $event, $title, $filters, \DateTimeInterface $createdAt, ?string $recipient = null)
    {
        $this->event     = $event;
        $this->title     = $title;
        $this->filters   = $filters;
        $this->createdAt = $createdAt;

        $this->sheets = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->recipients = [];

        if ($recipient) {
            $this->recipients[] = $recipient;
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return Sheet[]
     */
    public function getSheets()
    {
        return $this->sheets->toArray();
    }

    /**
     * @return Message|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * Get processedAt
     *
     * @return \DateTimeInterface|null
     */
    public function getProcessedAt()
    {
        return $this->processedAt;
    }

    /**
     * @param Sheet $sheet
     */
    public function addSheet(Sheet $sheet)
    {
        $this->sheets->set($sheet->getId(), $sheet);
    }

    /**
     * @param Message $message
     */
    public function setMessage(Message $message)
    {
        if ($message->getEvent() !== $this->event) {
            throw new \InvalidArgumentException();
        }

        $this->message = $message;
    }

    /**
     * @return array
     */
    public static function getRecipientChoices()
    {
        return [
            self::RECIPIENT_SHEET_OWNER,
            self::RECIPIENT_PARTICIPANTS,
            self::RECIPIENT_BILLING_CONTACT,
        ];
    }

    /**
     * @return string[]
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param $recipient
     *
     * @throw \InvalidArgumentException When $recipient does not belong to self::getRecipientChoices()
     */
    public function addRecipient($recipient)
    {
        if (!in_array($recipient, self::getRecipientChoices())) {
            throw new \InvalidArgumentException();
        }

        if (!in_array($recipient, $this->recipients)) {
            $this->recipients[] = $recipient;
        }
    }

    public function markAsSent(\DateTimeInterface $sentAt = null)
    {
        $this->sentAt = $sentAt ?: new \DateTime();
    }

    public function markAsProcessed(\DateTimeInterface $processedAt = null)
    {
        $this->processedAt = $processedAt ?: new \DateTime();
    }

    public function getUsers(): iterable
    {
        return $this->users->toArray();
    }

    public function addUser(User $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }
}
