<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Components\Happening\Participation\DisableEnableParticipation;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\Order\OrdersCancelledEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetChangedTypeEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetTitleCheckEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\CompletenessCalculator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SheetUpdatedEventSubscriber implements EventSubscriberInterface
{
    /** @var CompletenessCalculator */
    private $completenessCalculator;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var DisableEnableParticipation */
    private $disableEnableParticipation;

    /** @var SheetIndexerInterface */
    private $sheetIndexer;

    /**
     * @param CompletenessCalculator     $completenessCalculator
     * @param DisableEnableParticipation $disableEnableParticipation
     * @param SheetInfoGuesser           $sheetInfoGuesser
     * @param SheetRepositoryInterface   $sheetRepository
     * @param SheetIndexerInterface      $sheetIndexer
     */
    public function __construct(
        CompletenessCalculator $completenessCalculator,
        DisableEnableParticipation $disableEnableParticipation,
        SheetInfoGuesser $sheetInfoGuesser,
        SheetRepositoryInterface $sheetRepository,
        SheetIndexerInterface $sheetIndexer
    ) {
        $this->completenessCalculator     = $completenessCalculator;
        $this->disableEnableParticipation = $disableEnableParticipation;
        $this->sheetRepository            = $sheetRepository;
        $this->sheetInfoGuesser           = $sheetInfoGuesser;
        $this->sheetIndexer = $sheetIndexer;
    }

    /**
     * @param SheetUpdatedEvent $sheetUpdatedEvent
     */
    public function onSheetUpdated(SheetUpdatedEvent $sheetUpdatedEvent)
    {
        $this->completenessCalculator->calculateCompleteness($sheetUpdatedEvent->getSheet());
    }

    /**
     * @param SheetChangedTypeEvent $sheetChangedTypeEvent
     */
    public function onChangeType(SheetChangedTypeEvent $sheetChangedTypeEvent)
    {
        $this->completenessCalculator->calculateCompleteness($sheetChangedTypeEvent->getSheet());

        foreach ($sheetChangedTypeEvent->getSheet()->getUsers() as $user) {
            $this->disableEnableParticipation->resolveParticipationsForUser(
                $sheetChangedTypeEvent->getSheet()->getEvent(),
                $user
            );
        }
    }

    /**
     * @param SheetTitleCheckEvent $sheetTitleCheckEvent
     */
    public function onSheetTitleCheck(SheetTitleCheckEvent $sheetTitleCheckEvent)
    {
        $sheet             = $sheetTitleCheckEvent->getSheet();
        $guessedSheetTitle = $this->sheetInfoGuesser->guessSheetTitle($sheet, $sheet->getEvent()->getFallback());

        if ($guessedSheetTitle !== $sheet->getTitle()) {
            $sheet = $sheetTitleCheckEvent->getSheet();
            $sheet->setTitle($guessedSheetTitle);

            $this->sheetRepository->set($sheet);
        }
    }

    public function onOrdersCancelled(OrdersCancelledEvent $ordersCancelledEvent): void
    {
        $this->sheetIndexer->reindexSheets([$ordersCancelledEvent->sheet]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_UPDATED      => 'onSheetUpdated',
            Events::SHEET_CHANGED_TYPE => 'onChangeType',
            Events::SHEET_TITLE_CHECK  => 'onSheetTitleCheck',
            Events::SHEET_ORDERS_CANCELLED => 'onOrdersCancelled',
        ];
    }
}
