<?php

namespace Proximum\Vimeet\Domain\Model;

class Contact
{
    /** @var Event */
    private $event;

    /** @var User */
    private $user;

    /** @var User */
    private $contact;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var int|null */
    private $evaluation;

    /** @var string */
    private $comment = '';

    public function __construct(Event $event, User $user, User $contact, \DateTimeInterface $createdAt)
    {
        $this->event = $event;
        $this->user = $user;
        $this->contact = $contact;
        $this->createdAt = $createdAt;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getContact(): User
    {
        return $this->contact;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getEvaluation(): ?int
    {
        return $this->evaluation;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}
