<?php

namespace Proximum\Vimeet\Application\View\Agenda;

class UnavailabilityView extends AbstractTimeEntityView
{
    /** @var int */
    public $id;

    /** @var string */
    public $timeZone;

    /** @var null|string */
    public $message;

    /** @var bool */
    public $isCreatedByUser;

    /** @var bool */
    public $isDeletableByUser;

    public function __construct(
        int $id,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        string $timeZone,
        ?string $message,
        bool $isCreatedByUser,
        bool $isDeletableByUser
    ) {
        $this->id = $id;
        $this->begin = $begin;
        $this->end = $end;
        $this->timeZone = $timeZone;
        $this->message = $message;
        $this->isCreatedByUser = $isCreatedByUser;
        $this->isDeletableByUser = $isDeletableByUser;
    }

    /**
     * @return bool
     */
    public function hasMessage(): bool
    {
        return null !== $this->message;
    }
}
