<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\Template\SheetTemplateUpdatedEvent;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\ObjectsCollectionBlockCanNotContainForbiddenObjectsException;
use Proximum\Vimeet\Domain\Template\Exception\ObjectsCollectionBlockCanNotContainOtherBlockException;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplatePreviewResolver;

/**
 * Save SheetTemplate value
 */
class SaveHandler
{
    /** @var SheetTemplateRepositoryInterface */
    private $templateRepository;

    /** @var TemplatePreviewResolver */
    private $templatePreviewResolver;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var JobQueueInterface */
    private $jobQueue;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    public function __construct(
        SheetTemplateRepositoryInterface $templateRepository,
        TemplatePreviewResolver $templatePreviewResolver,
        TemplateDataFactory $templateDataFactory,
        JobQueueInterface $jobQueue,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->templateRepository = $templateRepository;
        $this->templatePreviewResolver = $templatePreviewResolver;
        $this->templateDataFactory = $templateDataFactory;
        $this->jobQueue = $jobQueue;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    /**
     * @param Save $save
     *
     * @throws ObjectsCollectionBlockCanNotContainForbiddenObjectsException
     * @throws ObjectsCollectionBlockCanNotContainOtherBlockException
     */
    public function handle(Save $save)
    {
        $save->template->setValue($save->value);
        $this->templateDataFactory->createFromTemplate($save->template);

        $this->templateRepository->set($save->template);

        $this->templatePreviewResolver->resolve($save->template);

        $this->jobQueue->indexSheetsBySheetTemplate($save->template);

        $this->delayedEventDispatcher->dispatch(
            Events::SHEET_TEMPLATE_UPDATED,
            new SheetTemplateUpdatedEvent($save->template)
        );
    }
}
