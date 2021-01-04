<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Stay;

use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class AddCommentHandler
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

    public function handle(AddComment $command): void
    {
        $this->extraDataRepository->removeForUserAndEventAndName(
            $command->user,
            $command->event,
            Type::ROOMING_COMMENT
        );

        if (!empty($command->comment)) {
            $extraData = new ExtraData(
                $command->user,
                $command->event,
                Type::ROOMING_COMMENT,
                $command->comment,
                $this->dateTime
            );
            $this->extraDataRepository->add($extraData);
        }
    }
}
