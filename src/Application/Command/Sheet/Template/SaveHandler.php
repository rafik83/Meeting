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
use Proximum\Vimeet\Domain\Template\TemplatePreviewResolver;

class SaveHandler
{
    /**
     * @var SheetTemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var TemplatePreviewResolver
     */
    private $templatePreviewResolver;

    /**
     * SaveHandler constructor.
     *
     * @param SheetTemplateRepositoryInterface $templateRepository
     * @param TemplatePreviewResolver          $templatePreviewResolver
     */
    public function __construct(
        SheetTemplateRepositoryInterface $templateRepository,
        TemplatePreviewResolver $templatePreviewResolver
    ) {
        $this->templateRepository      = $templateRepository;
        $this->templatePreviewResolver = $templatePreviewResolver;
    }

    /**
     * @param Save $save
     */
    public function handle(Save $save)
    {
        $save->template->setValue($save->value);

        $this->templateRepository->set($save->template);

        // resolve preview objects
        $this->templatePreviewResolver->resolve($save->template);
    }
}
