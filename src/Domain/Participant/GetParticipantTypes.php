<?php

namespace Proximum\Vimeet\Domain\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class GetParticipantTypes
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @return Type[]
     */
    public function handle(Participant $participant): array
    {
        $sheet = $participant->getSheet();

        if (!$sheet->hasGroup()) {
            return [$sheet->getType()];
        }

        return $this->typeRepository->getTypesByUserIds(
            $sheet->getEvent(),
            [$participant->getUser()->getId()]
        );
    }
}
