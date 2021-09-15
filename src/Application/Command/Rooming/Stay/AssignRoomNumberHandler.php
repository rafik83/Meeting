<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Stay;

use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;

class AssignRoomNumberHandler
{
    /** @var StayRepositoryInterface */
    private $stayRepository;

    public function __construct(StayRepositoryInterface $stayRepository)
    {
        $this->stayRepository = $stayRepository;
    }

    public function handle(AssignRoomNumber $command): void
    {
        $command->stay->setRoomNumber($command->roomNumber);

        $this->stayRepository->update($command->stay);
    }
}
