<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateRemoveField;

class DuplicateHandler
{
    /**
     * @var SheetTemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var TemplateRemoveField
     */
    private $templateRemoveField;

    /**
     * DuplicateHandler constructor.
     *
     * @param SheetTemplateRepositoryInterface $templateRepository
     * @param \DateTimeInterface               $dateTime
     * @param TemplateRemoveField              $templateRemoveField
     */
    public function __construct(
        SheetTemplateRepositoryInterface $templateRepository,
        \DateTimeInterface $dateTime,
        TemplateRemoveField $templateRemoveField
    ) {
        $this->templateRepository  = $templateRepository;
        $this->dateTime            = $dateTime;
        $this->templateRemoveField = $templateRemoveField;
    }

    /**
     * @param Duplicate $duplicate
     *
     * @return DuplicateResult
     */
    public function handle(Duplicate $duplicate)
    {
        if (null !== $duplicate->template->getEvent()
            && $duplicate->template->getEvent() !== $duplicate->event
        ) {
            $duplicate->template->setValue($this->templateRemoveField->remove($duplicate->template, 'products', []));
            $duplicate->template->setValue($this->templateRemoveField->remove($duplicate->template, 'nomenclature', []));
        }

        // Duplicate SheetTemplate
        $template = $duplicate->template->duplicate(
            $duplicate->title,
            $duplicate->template->getValue(),
            $this->dateTime,
            $duplicate->template->getPreview()
        );
        
        if ($duplicate->event) {
            $template->setEvent($duplicate->event);
        }

        $this->templateRepository->add($template);

        return new DuplicateResult($template);
    }
}
