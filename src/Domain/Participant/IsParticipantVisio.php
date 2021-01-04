<?php

namespace Proximum\Vimeet\Domain\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class IsParticipantVisio
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    public function __construct(ExtraDataRepositoryInterface $extraDataRepository)
    {
        $this->extraDataRepository = $extraDataRepository;
    }

    public function isSatisfiedBy(Participant $participant): bool
    {
        if (true === $participant->getEvent()->getConfiguration()->isVisio()) {
            return true;
        }

        $extraData = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $participant->getEvent(),
            Type::IS_PARTICIPANT_VISIO,
            $participant->getUser()
        );

        return $extraData instanceof ExtraData;
    }
}
