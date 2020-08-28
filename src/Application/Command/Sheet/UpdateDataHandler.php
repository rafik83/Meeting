<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\MediaCollection;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UpdateDataHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var BuyableObjectResolver */
    private $buyableObjectResolver;

    /** @var RemoveDataHandler */
    private $removeDataHandler;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        BuyableObjectResolver $buyableObjectResolver,
        RemoveDataHandler $removeDataHandler,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->buyableObjectResolver = $buyableObjectResolver;
        $this->removeDataHandler = $removeDataHandler;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(UpdateData $command): void
    {
        if ($command->templateObject instanceof MediaCollection &&
            0 === \count($command->templateObject->getMedias())
        ) {
            $this->removeDataHandler->handle(new RemoveData(
                $command->templateData,
                $command->templateObject,
                $command->sheet
            ));
        }

        if (null !== $command->templateObject) {
            $this->buyableObjectResolver->updateCart($command->sheet, $command->templateObject);
        }

        $this->sheetRepository->set($command->sheet->setData($command->templateData->getData()));

        $sheetUpdatedEvent = new SheetUpdatedEvent($command->sheet);
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent);
    }
}
