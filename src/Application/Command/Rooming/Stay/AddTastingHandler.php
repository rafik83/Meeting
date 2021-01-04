<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Stay;

use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class AddTastingHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(ExtraDataRepositoryInterface $extraDataRepository, \DateTimeInterface $dateTime)
    {
        $this->extraDataRepository = $extraDataRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(AddTasting $command): void
    {
        $this->extraDataRepository->removeForUserAndEventAndName(
            $command->user,
            $command->event,
            Type::ROOMING_TASTING
        );

        if (!empty($command->comment)) {
            $extraData = new ExtraData(
                $command->user,
                $command->event,
                Type::ROOMING_TASTING,
                $command->comment,
                $this->dateTime
            );
            $this->extraDataRepository->add($extraData);
        }
    }
}
