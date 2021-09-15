<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet\Template;

use Proximum\Vimeet\Application\Command\Sheet\Template\GenerateTaggedNomenclatureFilter;
use Proximum\Vimeet\Application\Command\Sheet\Template\GenerateTaggedNomenclatureFilterHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\Template\SheetTemplateUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SheetTemplateUpdatedEventSubscriber implements EventSubscriberInterface
{
    /** @var GenerateTaggedNomenclatureFilterHandler */
    private $generateTaggedNomenclatureFilter;

    public function __construct(
        GenerateTaggedNomenclatureFilterHandler $generateTaggedNomenclatureFilter
    ) {
        $this->generateTaggedNomenclatureFilter = $generateTaggedNomenclatureFilter;
    }

    public function onSheetTemplateUpdated(SheetTemplateUpdatedEvent $sheetTemplateUpdatedEvent): void
    {
        $this->generateTaggedNomenclatureFilter->handle(
            new GenerateTaggedNomenclatureFilter($sheetTemplateUpdatedEvent->sheetTemplate->getEvent())
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::SHEET_TEMPLATE_UPDATED => 'onSheetTemplateUpdated',
        ];
    }
}
