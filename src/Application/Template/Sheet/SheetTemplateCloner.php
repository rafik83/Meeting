<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum Vimeet
 *
 * @author Elao <contact@elao.com>
 */


namespace Proximum\Vimeet\Application\Template\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;

class SheetTemplateCloner
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
     * @param \DateTimeInterface               $dateTime
     */
    public function __construct(SheetTemplateRepositoryInterface $sheetTemplateRepository, \DateTimeInterface $dateTime)
    {
        $this->sheetTemplateRepository = $sheetTemplateRepository;
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
        $sheetTemplate = new SheetTemplate(
            $title,
            $sheetTemplate->getValue(),
            $sheetTemplate->getLocales(),
            $sheetTemplate->getFallback(),
            $this->dateTime
        );
        $sheetTemplate->setEvent($event);

        $this->sheetTemplateRepository->add($sheetTemplate);

        return $sheetTemplate;
    }
}
