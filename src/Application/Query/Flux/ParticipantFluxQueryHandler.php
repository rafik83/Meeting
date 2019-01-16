<?php

namespace Proximum\Vimeet\Application\Query\Flux;

use Proximum\Vimeet\Application\View\Flux\ParticipantListView;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipantFluxQueryHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    public function __construct(ParticipantRepositoryInterface $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    public function handle(ParticipantFluxQuery $query): ParticipantListView
    {
        return new ParticipantListView([]);
    }
}
