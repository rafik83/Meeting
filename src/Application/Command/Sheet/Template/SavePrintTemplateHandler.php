<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;

class SavePrintTemplateHandler
{
    /** @var SheetTemplateRepositoryInterface */
    private $templateRepository;

    /**
     * @param SheetTemplateRepositoryInterface $templateRepository
     */
    public function __construct(SheetTemplateRepositoryInterface $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    /**
     * @param SavePrintTemplate $save
     */
    public function handle(SavePrintTemplate $save)
    {
        $save->template->setPrintValue($save->value);
        $this->templateRepository->set($save->template);
    }
}
