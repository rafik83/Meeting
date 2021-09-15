<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet\Analytics;

use Proximum\Vimeet\Application\Command\Sheet\AddView;
use Proximum\Vimeet\Application\Command\Sheet\AddViewHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetViewedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SheetsUserActivityEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var AddViewHandler
     */
    private $addViewHandler;

    public function __construct(AddViewHandler $addViewHandler)
    {
        $this->addViewHandler = $addViewHandler;
    }

    public function onSheetView(SheetViewedEvent $sheetViewedEvent): void
    {
        $this->addViewHandler->handle(new AddView($sheetViewedEvent->getUser(), $sheetViewedEvent->getSheet()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::SHEET_VIEWED => 'onSheetView',
        ];
    }
}
