<?php

namespace Proximum\Vimeet\Domain\Event\SheetTemplate;

use Proximum\Vimeet\Application\Template\Sheet\SheetTemplateCloner;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;

class Duplicator
{
    /** @var SheetTemplateRepositoryInterface */
    private $sheetTemplateRepository;

    /** @var SheetTemplateCloner */
    private $sheetTemplateCloner;

    /**
     * @param SheetTemplateRepositoryInterface $sheetTemplateRepository
     * @param SheetTemplateCloner              $sheetTemplateCloner
     */
    public function __construct(
        SheetTemplateRepositoryInterface $sheetTemplateRepository,
        SheetTemplateCloner $sheetTemplateCloner
    ) {
        $this->sheetTemplateRepository = $sheetTemplateRepository;
        $this->sheetTemplateCloner     = $sheetTemplateCloner;
    }

    /**
     * @param Event                 $event
     * @param DuplicatorDataStorage $duplicatorDataStorage
     *
     * @return DuplicatorDataStorage
     */
    public function duplicate(Event $event, DuplicatorDataStorage $duplicatorDataStorage): DuplicatorDataStorage
    {
        $sheetTemplates = $this
            ->sheetTemplateRepository
            ->getTemplateForGivenEvent($event->getDuplicatedFrom())
        ;

        foreach ($sheetTemplates as $sheetTemplate) {
            $clonedTemplate = $this->sheetTemplateCloner->duplicate(
                $sheetTemplate,
                $event,
                $sheetTemplate->getTitle()
            );

            $duplicatorDataStorage->sheetTemplates[$sheetTemplate->getId()] = $clonedTemplate;
        }

        return $duplicatorDataStorage;
    }
}
