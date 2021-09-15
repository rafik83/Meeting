<?php

namespace Proximum\Vimeet\Application\Command\Event\ExtraParameter;

use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class UpdateHandler
{
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param \DateTimeInterface                $dateTime
     */
    public function __construct(
        ExtraParameterRepositoryInterface $extraParameterRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->extraParameterRepository = $extraParameterRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Update $command
     */
    public function handle(Update $command)
    {
        $command->extraParameter->update($command->name, $command->value, $this->dateTime);

        $this->extraParameterRepository->set($command->extraParameter);
    }
}
