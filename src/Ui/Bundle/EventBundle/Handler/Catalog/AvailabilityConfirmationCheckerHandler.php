<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Event\Day\EventOver;
use Proximum\Vimeet\Domain\KeyDates\Checker\AgendaAccessChecker;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Model\Type as ParticipantType;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class AvailabilityConfirmationCheckerHandler
{
    const ROUTE_AVAILABILITY_CONFIRMATION = 'event_availability_confirmation';

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var EventOver */
    private $eventOver;

    /** @var AgendaAccessChecker */
    private $agendaAccessChecker;

    /** @var string */
    private $featureAvailabilityConfirmationActivated;

    /**
     * @param AgendaAccessChecker          $agendaAccessChecker
     * @param EventOver                    $eventOver
     * @param FlashBagInterface            $flashBag
     * @param ExtraDataRepositoryInterface $extraDataRepository
     * @param RouterInterface              $router
     * @param string                       $featureAvailabilityConfirmationActivated
     */
    public function __construct(
        AgendaAccessChecker $agendaAccessChecker,
        EventOver $eventOver,
        FlashBagInterface $flashBag,
        ExtraDataRepositoryInterface $extraDataRepository,
        RouterInterface $router,
        string $featureAvailabilityConfirmationActivated
    ) {
        $this->eventOver = $eventOver;
        $this->extraDataRepository = $extraDataRepository;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->agendaAccessChecker = $agendaAccessChecker;
        $this->featureAvailabilityConfirmationActivated = $featureAvailabilityConfirmationActivated;
    }

    /**
     * @param AvailabilityConfirmationChecker $command
     *
     * @return AvailabilityConfirmationCheckerView
     */
    public function handle(AvailabilityConfirmationChecker $command): AvailabilityConfirmationCheckerView
    {
        if (false === (bool) $this->featureAvailabilityConfirmationActivated
            || true === $this->eventOver->isEventOver($command->event)
            || false === $this->agendaAccessChecker->allowedToAccess($command->event)
            || ParticipantType::TYPE_MANAGEMENT_UNAVAILABLE !== $command->sheet->getType()->getAvailabilityType()
        ) {
            return new AvailabilityConfirmationCheckerView(AvailabilityConfirmationCheckerView::ALLOWED_TO_ACCESS, null);
        }

        $extraData = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $command->event,
            Type::AVAILABILITY_CONFIRMATION,
            $command->user
        );

        if ($extraData instanceof ExtraData) {
            return new AvailabilityConfirmationCheckerView(AvailabilityConfirmationCheckerView::ALLOWED_TO_ACCESS, null);
        }

        $this->flashBag->add($command->origin, $command->sheet->getId());

        return new AvailabilityConfirmationCheckerView(
            AvailabilityConfirmationCheckerView::REDIRECT,
            $this->router->generate(self::ROUTE_AVAILABILITY_CONFIRMATION, ['sheet' => $command->sheet->getId()])
        );
    }
}
