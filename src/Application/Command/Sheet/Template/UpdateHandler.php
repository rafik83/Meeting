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

class UpdateHandler
{
    /**
     * @var SheetTemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param SheetTemplateRepositoryInterface $templateRepository
     */
    public function __construct(SheetTemplateRepositoryInterface $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $update->template->update($update->title, $update->fallback);
        $this->templateRepository->set($update->template);
    }
}
