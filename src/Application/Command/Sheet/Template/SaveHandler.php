<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplatePreviewResolver;

/**
 * Save SheetTemplate value
 */
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

    /** @var JobQueueInterface */
    private $jobQueue;

    /**
     * SaveHandler constructor.
     *
     * @param SheetTemplateRepositoryInterface $templateRepository
     * @param TemplatePreviewResolver          $templatePreviewResolver
     * @param JobQueueInterface                $jobQueue
     */
    public function __construct(
        SheetTemplateRepositoryInterface $templateRepository,
        TemplatePreviewResolver $templatePreviewResolver,
        JobQueueInterface $jobQueue
    ) {
        $this->templateRepository      = $templateRepository;
        $this->templatePreviewResolver = $templatePreviewResolver;
        $this->jobQueue                = $jobQueue;
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

        $this->jobQueue->indexSheetsBySheetTemplate($save->template);
    }
}
