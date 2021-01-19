<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Sheet\ChangeType\CancelPackage;
use Proximum\Vimeet\Application\Command\Sheet\ChangeType\CancelPackageHandler;
use Proximum\Vimeet\Application\Components\Registration\StepManager;
use Proximum\Vimeet\Application\Components\Sheet\Request\EnableDisableManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Package\MustSelectPackageEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetChangedTypeEvent;
use Proximum\Vimeet\Application\Exception\Sheet\InvoicedSheetException;
use Proximum\Vimeet\Application\Exception\Sheet\SheetWithMeetingsException;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class ChangeTypeHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /** @var TranslatorInterface */
    private $translator;

    /** @var \DateTimeInterface */
    private $datetime;

    /** @var StepManager */
    private $registrationStepManager;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var EnableDisableManager */
    private $enableDisableManager;

    /** @var CancelPackageHandler */
    private $cancelPackageHandler;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /**
     * @param SheetRepositoryInterface   $sheetRepository
     * @param OrderRepositoryInterface   $orderRepository
     * @param CancelPackageHandler       $cancelPackageHandler
     * @param MeetingRepositoryInterface $meetingRepository
     * @param EnableDisableManager       $enableDisableManager
     * @param TranslatorInterface        $translator
     * @param DelayedEventDispatcher     $eventDispatcher
     * @param StepManager                $registrationStepManager
     * @param \DateTimeInterface         $datetime
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        OrderRepositoryInterface $orderRepository,
        CancelPackageHandler $cancelPackageHandler,
        MeetingRepositoryInterface $meetingRepository,
        EnableDisableManager $enableDisableManager,
        TranslatorInterface $translator,
        DelayedEventDispatcher $eventDispatcher,
        StepManager $registrationStepManager,
        \DateTimeInterface $datetime
    ) {
        $this->sheetRepository         = $sheetRepository;
        $this->orderRepository         = $orderRepository;
        $this->cancelPackageHandler    = $cancelPackageHandler;
        $this->translator              = $translator;
        $this->eventDispatcher         = $eventDispatcher;
        $this->datetime                = $datetime;
        $this->registrationStepManager = $registrationStepManager;
        $this->meetingRepository       = $meetingRepository;
        $this->enableDisableManager    = $enableDisableManager;
    }

    /**
     * @param ChangeType $changeType
     *
     * @throws SheetWithMeetingsException
     * @throws InvoicedSheetException
     */
    public function handle(ChangeType $changeType)
    {
        $this->denyAccessIfAtLeastOneOrderIsInvoiced($changeType->sheet);
        $this->denyAccessIfAtLeastOneMeeting($changeType->sheet);
        $previousType = $changeType->sheet->getType();

        if (null === $changeType->type || $changeType->type === $previousType) {
            return;
        }

        // get previous package
        $previousPackage = $changeType->sheet->getType()->getPackage();

        // update sheet type
        $changeType->sheet->updateType($changeType->type);

        // As the sheet is removed from the catalog in the updateType method
        // The requests need to be disable
        $this->enableDisableManager->update($changeType->sheet, false);

        $this->sheetRepository->set($changeType->sheet);

        // get current package
        $currentPackage = $changeType->type->getPackage();

        // if previous package different of new one, cancel orders
        if ($previousPackage !== $currentPackage) {
            $this->cancelPackageHandler->handle(new CancelPackage($changeType->sheet));
        }

        // reset registration step to redirect participant on registration
        foreach ($changeType->sheet->getParticipants() as $participant) {
            $this->registrationStepManager->resetRegistrationStep($participant);
        }

        // dispatch SHEET_CHANGED_TYPE event
        $this->eventDispatcher->dispatch(
            Events::SHEET_CHANGED_TYPE,
            new SheetChangedTypeEvent(
                $changeType->sheet,
                $changeType->admin,
                $this->datetime,
                $this->translator->trans('admin.sheet.trace.changed_type_comment', [
                    '%fromType%' => $previousType->getTitle($changeType->locale),
                    '%toType%'   => $changeType->type->getTitle($changeType->locale),
                ]),
                $previousType->getTitle($changeType->locale),
                $changeType->locale
            )
        );

        // trigger event to generate must select package notification if user has no order
        $ordersUpdated = new MustSelectPackageEvent($changeType->sheet);
        $this->eventDispatcher->dispatch(Events::MUST_SELECT_PACKAGE, $ordersUpdated);
    }

    /**
     * Throw exception if current sheet has at least one order invoiced
     * When user attempt to change sheet type
     *
     * @param Sheet $sheet
     *
     * @throws InvoicedSheetException
     */
    private function denyAccessIfAtLeastOneOrderIsInvoiced(Sheet $sheet): void
    {
        if (true === $this->orderRepository->hasInvoice($sheet)) {
            throw new InvoicedSheetException('Sheet type cannot be changed');
        }
    }

    /**
     * @param Sheet $sheet
     *
     * @throws SheetWithMeetingsException
     */
    private function denyAccessIfAtLeastOneMeeting(Sheet $sheet): void
    {
        if (0 < $this->meetingRepository->countMeetingsOfSheet($sheet)) {
            throw new SheetWithMeetingsException('Sheet type cannot be changed as Sheet has meetings');
        }
    }
}
