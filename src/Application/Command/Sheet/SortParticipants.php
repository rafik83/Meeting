<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet;

class SortParticipants implements Command
{
    /** @var Sheet */
    private $sheet;

    /** @var int[] */
    private $participantsRank;

    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;

        foreach ($sheet->getParticipantsArray() as $participant) {
            $this->participantsRank[$participant->getId()] = $participant->getRank();
        }
    }

    public function __isset(int $id): bool
    {
        return isset($this->participantsRank[$id]);
    }

    public function __set(int $id, int $rank)
    {
        $this->participantsRank[$id] = $rank;
    }

    public function __get(int $id): int
    {
        return $this->getParticipantRank($id);
    }

    public function getParticipantsRank(): array
    {
        return $this->participantsRank;
    }

    public function getParticipantRank($id): int
    {
        return $this->participantsRank[$id];
    }

    public function getSheet(): Sheet
    {
        return $this->sheet;
    }
}
