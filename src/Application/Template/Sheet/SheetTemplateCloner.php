<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum Vimeet
 *
 * @author Elao <contact@elao.com>
 */


namespace Proximum\Vimeet\Application\Template\Sheet;

use Proximum\Vimeet\Application\Nomenclature\NomenclatureCloner;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SheetTemplateCloner
{
    /**
     * @var SheetTemplateRepositoryInterface
     */
    private $sheetTemplateRepository;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var NomenclatureCloner
     */
    private $nomenclatureCloner;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * SheetTemplateCloner constructor.
     *
     * @param SheetTemplateRepositoryInterface $sheetTemplateRepository
     * @param TemplateDataFactory              $templateDataFactory
     * @param NomenclatureCloner               $nomenclatureCloner
     * @param \DateTimeInterface               $dateTime
     */
    public function __construct(
        SheetTemplateRepositoryInterface $sheetTemplateRepository,
        TemplateDataFactory $templateDataFactory,
        NomenclatureCloner $nomenclatureCloner,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetTemplateRepository = $sheetTemplateRepository;
        $this->templateDataFactory     = $templateDataFactory;
        $this->nomenclatureCloner      = $nomenclatureCloner;
        $this->dateTime                = $dateTime;
    }

    /**
     * @param SheetTemplate $sheetTemplate
     * @param Event         $event
     * @param string        $title
     *
     * @return SheetTemplate
     */
    public function duplicate(SheetTemplate $sheetTemplate, Event $event, $title)
    {
        // Clone tempalte
        $sheetTemplateClone = new SheetTemplate(
            $title,
            $sheetTemplate->getValue(),
            $sheetTemplate->getLocales(),
            $sheetTemplate->getFallback(),
            $this->dateTime
        );

        // We have to keep the original event to find the right nomenclatures in the template data builder
        $sheetTemplateClone->setEvent($sheetTemplate->getEvent());

        // Clone nomenclature
        $template = $this->cloneNomenclatures($event, $sheetTemplateClone);

        // Update template
        $sheetTemplateClone->setValue($template->getConfig());

        // Now we can set the proper event
        $sheetTemplateClone->setEvent($event);

        // Save
        $this->sheetTemplateRepository->add($sheetTemplateClone);

        return $sheetTemplateClone;
    }

    /**
     * @param Event         $event
     * @param SheetTemplate $sheetTemplate
     *
     * @return TemplateData
     */
    private function cloneNomenclatures(Event $event, SheetTemplate $sheetTemplate)
    {
        $template = $this->templateDataFactory->createFromSheetTemplate($sheetTemplate);
        $objects  = $template->getNomenclatureObjects();

        foreach ($objects as $object) {
            if ($object->getNomenclatureModel()->getEvent() !== $event) {
                $original = $object->getNomenclatureModel();
                $clone    = $this->nomenclatureCloner->dublicateIfNotExists($original, $event);

                $object->setNomenclature($clone);
            }
        }

        return $template;
    }
}
