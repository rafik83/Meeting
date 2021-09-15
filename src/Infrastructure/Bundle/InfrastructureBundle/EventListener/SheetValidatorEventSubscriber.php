<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Proximum\Vimeet\Application\Components\Sheet\SheetValidator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetAcceptedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SheetValidatorEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var SheetValidator
     */
    private $sheetValidator;

    /**
     * @param SheetValidator $sheetValidator
     */
    public function __construct(SheetValidator $sheetValidator)
    {
        $this->sheetValidator = $sheetValidator;
    }

    /**
     * @param SheetAcceptedEvent $event
     */
    public function validateSheetAccepted(SheetAcceptedEvent $event)
    {
        $this->sheetValidator->validate($event->getSheet());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_ACCEPTED => 'validateSheetAccepted',
        ];
    }
}
