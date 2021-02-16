<?php

namespace Proximum\Vimeet\Application\Template\Sheet;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\Template\SheetTemplateUpdatedEvent;
use Proximum\Vimeet\Application\Nomenclature\NomenclatureCloner;
use Proximum\Vimeet\Application\Template\TemplateCloner;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateRemoveField;

class SheetTemplateCloner extends TemplateCloner
{
    /**
     * @var SheetTemplateRepositoryInterface
     */
    private $sheetTemplateRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var TemplateRemoveField
     */
    private $templateRemoveField;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    public function __construct(
        SheetTemplateRepositoryInterface $sheetTemplateRepository,
        TemplateDataFactory $templateDataFactory,
        NomenclatureCloner $nomenclatureCloner,
        \DateTimeInterface $dateTime,
        TemplateRemoveField $templateRemoveField,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        parent::__construct($templateDataFactory, $nomenclatureCloner);

        $this->sheetTemplateRepository = $sheetTemplateRepository;
        $this->dateTime = $dateTime;
        $this->templateRemoveField = $templateRemoveField;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    /**
     * @param SheetTemplate $template
     * @param Event         $event
     * @param string        $title
     *
     * @return SheetTemplate
     */
    public function duplicate(SheetTemplate $template, Event $event, $title)
    {
        $clone = new SheetTemplate(
            $title,
            $template->getValue(),
            $template->getLocales(),
            $template->getFallback(),
            $this->dateTime,
            $template->getPreview(),
            $template->getEvent()
        );

        if ($event !== $template->getEvent()) {
            $this->switchEvent($event, $clone);
            $clone->setValue($this->templateRemoveField->remove($clone, 'products', []));
        }

        $this->sheetTemplateRepository->add($clone);

        $this->delayedEventDispatcher->dispatch(
            Events::SHEET_TEMPLATE_UPDATED,
            new SheetTemplateUpdatedEvent($clone)
        );

        return $clone;
    }
}
