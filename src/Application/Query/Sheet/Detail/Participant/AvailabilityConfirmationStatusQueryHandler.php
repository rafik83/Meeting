<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Detail\Participant;

use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AvailabilityConfirmationView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AvailabilityConfirmedView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AvailabilityNotConfirmedView;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class AvailabilityConfirmationStatusQueryHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /**
     * @param ExtraDataRepositoryInterface $extraDataRepository
     */
    public function __construct(ExtraDataRepositoryInterface $extraDataRepository)
    {
        $this->extraDataRepository = $extraDataRepository;
    }

    /**
     * @param AvailabilityConfirmationStatusQuery $query
     *
     * @return AvailabilityConfirmationView
     */
    public function handle(AvailabilityConfirmationStatusQuery $query): AvailabilityConfirmationView
    {
        $extraData = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $query->event,
            Type::AVAILABILITY_CONFIRMATION,
            $query->user
        );

        return null !== $extraData ? new AvailabilityConfirmedView() : new AvailabilityNotConfirmedView();
    }
}
