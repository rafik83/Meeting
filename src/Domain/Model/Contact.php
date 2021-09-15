<?php

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;
use InvalidArgumentException;

class Contact
{
    public const ORIGIN_MEETING = 'meeting';
    public const ORIGIN_SCAN = 'scan';
    public const ORIGIN_PRIVATE_CHAT_VISIO = 'private_chat_visio';
    public const ORIGIN_NONE = 'none';

    /** @var Event */
    private $event;

    /** @var User */
    private $user;

    /** @var User */
    private $contact;

    private DateTimeInterface $createdAt;

    /** @var int|null */
    private $evaluation;

    private ?DateTimeInterface $evaluatedAt;

    /** @var string|null */
    private $comment;

    /** @var string */
    private $origin;

    public function __construct(
        Event $event,
        User $user,
        User $contact,
        DateTimeInterface $createdAt,
        string $origin
    )
    {
        $this->event = $event;
        $this->user = $user;
        $this->contact = $contact;
        $this->createdAt = $createdAt;
        $this->origin = $origin;
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

    public function getEvaluatedAt(): ?DateTimeInterface
    {
        return $this->evaluatedAt;
    }

    public function hasEvaluation(): bool
    {
        return null !== $this->evaluation;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function hasComment(): bool
    {
        return null !== $this->comment;
    }

    public function isScanned(): bool
    {
        return self::ORIGIN_SCAN === $this->origin;
    }

    /** @throws InvalidArgumentException */
    public function setEvaluation(int $evaluation, ?DateTimeInterface $evaluatedAt = null): void
    {
        if ($this->origin === self::ORIGIN_MEETING && $evaluatedAt === null) {
            throw new InvalidArgumentException('evaluatedAt must not be null for meeting evaluation');
        }

        $this->evaluation = $evaluation;
        $this->evaluatedAt = $evaluatedAt;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }
}
