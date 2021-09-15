<?php

namespace Proximum\Vimeet\Application\Command\User\Phone;

use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class IgnoreConfirmationHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param ExtraDataRepositoryInterface $extraDataRepository
     * @param \DateTimeInterface           $dateTime
     */
    public function __construct(
        ExtraDataRepositoryInterface $extraDataRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->extraDataRepository = $extraDataRepository;
        $this->dateTime            = $dateTime;
    }

    /**
     * @param IgnoreConfirmation $ignoreConfirmation
     */
    public function handle(IgnoreConfirmation $ignoreConfirmation): void
    {
        $extraData = $this
            ->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $ignoreConfirmation->event,
                Type::PHONE_CONFIRMATION_IGNORED,
                $ignoreConfirmation->participant->getUser()
            )
        ;

        if (null === $extraData) {
            $this->extraDataRepository->add(
                new ExtraData(
                    $ignoreConfirmation->participant->getUser(),
                    $ignoreConfirmation->event,
                    Type::PHONE_CONFIRMATION_IGNORED,
                    '',
                    $this->dateTime
                )
            );
        }
    }
}
