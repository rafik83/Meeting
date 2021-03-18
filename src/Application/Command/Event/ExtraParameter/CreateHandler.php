<?php

namespace Proximum\Vimeet\Application\Command\Event\ExtraParameter;

use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Exception\Event\ExtraParameter\ExtraParameterAlreadyExistForThisTypeAndEventException;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class CreateHandler
{
    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

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
     * @param Create $create
     *
     * @throws ExtraParameterAlreadyExistForThisTypeAndEventException
     */
    public function handle(Create $create)
    {
        if (null !== $this->extraParameterRepository->findByEventAndType($create->event, $create->type)) {
            throw new ExtraParameterAlreadyExistForThisTypeAndEventException();
        }

        $extraParameter = new ExtraParameter(
            $create->event,
            $create->type,
            $create->name,
            $create->value,
            $this->dateTime
        );

        $this->extraParameterRepository->add($extraParameter);
    }
}
