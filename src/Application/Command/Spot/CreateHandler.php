<?php

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Application\Exception\Spot\UniqueReferenceViolationException;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class CreateHandler
{
    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * CreateHandler constructor.
     *
     * @param SpotRepositoryInterface $spotRepository
     */
    public function __construct(SpotRepositoryInterface $spotRepository)
    {
        $this->spotRepository = $spotRepository;
    }

    /**
     * @param Create $create
     *
     * @throws UniqueReferenceViolationException
     */
    public function handle(Create $create)
    {
        if (null !== $this->spotRepository->findByReference($create->event, $create->reference)) {
            throw new UniqueReferenceViolationException($create->reference);
        }

        $spot = new Spot(
            $create->reference,
            $create->event,
            $create->size,
            $create->meetingCapacity,
            $create->seatCapacity,
            $create->active,
            $create->priority,
            $create->visio
        );

        $this->spotRepository->add($spot);
    }
}
