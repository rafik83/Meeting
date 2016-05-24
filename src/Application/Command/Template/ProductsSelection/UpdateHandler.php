<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Template\ProductsSelection;

use Proximum\Vimeet\Domain\Repository\Template\ProductsSelectionTemplateRepositoryInterface;

class UpdateHandler
{
    /**
     * @var ProductsSelectionTemplateRepositoryInterface
     */
    private $productsSelectionTemplateRepository;

    /**
     * @param ProductsSelectionTemplateRepositoryInterface $productsSelectionTemplateRepository
     */
    public function __construct(
        ProductsSelectionTemplateRepositoryInterface $productsSelectionTemplateRepository
    ) {
        $this->productsSelectionTemplateRepository = $productsSelectionTemplateRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $update->template->setTitle($update->title);
        $update->template->setValue($update->templateData->normalize());

        $this->productsSelectionTemplateRepository->set($update->template);
    }
}
