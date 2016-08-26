<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class UpdatePreviewHandler
{
    /**
     * @var SheetTemplateRepositoryInterface
     */
    private $sheetTemplateRepository;

    /**
     * UpdatePreviewHandler constructor.
     *
     * @param SheetTemplateRepositoryInterface $sheetTemplateRepository
     */
    public function __construct(SheetTemplateRepositoryInterface $sheetTemplateRepository)
    {
        $this->sheetTemplateRepository = $sheetTemplateRepository;
    }

    /**
     * @param UpdatePreview $updatePreview
     */
    public function handle(UpdatePreview $updatePreview)
    {
        $updatePreview->sheetTemplate->setPreview($updatePreview->previewObjects);

        $this->sheetTemplateRepository->set($updatePreview->sheetTemplate);
    }
}
