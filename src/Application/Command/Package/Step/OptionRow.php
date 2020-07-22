<?php

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Domain\Model\Participant;

class OptionRow
{
    /** @var int */
    private $quantity;

    /** @var Participant[] */
    private $participants;

    /** @var bool */
    public $isAttributable;

    public function __construct(int $quantity, array $participants = [], bool $isAttributable = false)
    {
        $this->quantity = $quantity;
        $this->participants = $participants;
        $this->isAttributable = $isAttributable;
    }

    public function getQuantity(): int
    {
        return !$this->isAttributable
            ? $this->quantity
            : \count($this->participants);
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return Participant[]
     */
    public function getParticipants(): array
    {
        return $this->participants;
    }

    /**
     * @param Participant[] $participants
     */
    public function setParticipants(array $participants): void
    {
        $this->participants = $participants;
    }
}
