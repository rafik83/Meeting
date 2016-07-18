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
use Proximum\Vimeet\Application\Template\TemplateCloner;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

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
        parent::__construct($templateDataFactory, $nomenclatureCloner);

        $this->sheetTemplateRepository = $sheetTemplateRepository;
        $this->dateTime                = $dateTime;
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
            $template->getEvent()
        );

        $this->switchEvent($event, $clone);

        $this->sheetTemplateRepository->add($clone);

        return $clone;
    }
}
