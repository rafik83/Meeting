<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\CartRowParticipant;
use Proximum\Vimeet\Domain\Model\Participant;

interface CartRowParticipantRepositoryInterface
{
    public function findByParticipant(Participant $participant): ?CartRowParticipant;

    /**
     * @param Participant[] $participants
     *
     * @return CartRowParticipant[]
     */
    public function findCartRowOnAttributableProductForParticipants(array $participants): array;
}
