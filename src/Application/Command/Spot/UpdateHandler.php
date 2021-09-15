<?php

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Application\Exception\Spot\PropertyNotSupportedException;
use Proximum\Vimeet\Application\Exception\Spot\SpotNotFoundException;
use Proximum\Vimeet\Application\Exception\Spot\UniqueReferenceViolationException;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class UpdateHandler
{
    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param SpotRepositoryInterface $spotRepository
     */
    public function __construct(SpotRepositoryInterface $spotRepository)
    {
        $this->spotRepository = $spotRepository;
    }

    /**
     * @param Update $update
     *
     * @throws UniqueReferenceViolationException
     * @throws PropertyNotSupportedException
     * @throws SpotNotFoundException
     */
    public function handle(Update $update)
    {
        if (!in_array($update->property, ['reference', 'size', 'meetingCapacity', 'seatCapacity', 'priority'])) {
            throw new PropertyNotSupportedException($update->property);
        }

        $spot = $this->spotRepository->find($update->event, $update->id);

        if (null === $spot) {
            throw new SpotNotFoundException();
        }

        if (
            'reference' === $update->property &&
            $update->value !== $spot->getReference() &&
            null !== $this->spotRepository->findByReference($update->event, $update->value)
        ) {
            throw new UniqueReferenceViolationException($update->value);
        }

        $this->spotRepository->set($spot->update($update->property, $update->value));
    }
}
