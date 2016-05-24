<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Template\ProductsSelection;

use Proximum\Vimeet\Domain\Model\Template\ProductsSelectionTemplate;
use Proximum\Vimeet\Domain\Repository\Template\ProductsSelectionTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ProductsSelectionTemplateFactory;

class CreateHandler
{
    /**
     * @var ProductsSelectionTemplateRepositoryInterface
     */
    private $productsSelectionTemplateRepository;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @param ProductsSelectionTemplateRepositoryInterface $productsSelectionTemplateRepository
     * @param ProductsSelectionTemplateFactory             $productsSelectionTemplateFactory
     * @param \DateTimeInterface                           $createdAt
     */
    public function __construct(
        ProductsSelectionTemplateRepositoryInterface $productsSelectionTemplateRepository,
        ProductsSelectionTemplateFactory $productsSelectionTemplateFactory,
        \DateTimeInterface $createdAt
    ) {
        $this->productsSelectionTemplateRepository = $productsSelectionTemplateRepository;
        $this->productsSelectionTemplateFactory    = $productsSelectionTemplateFactory;
        $this->createdAt                           = $createdAt;
    }

    /**
     * @param Create $create
     *
     * @return ProductsSelectionTemplate
     */
    public function handle(Create $create)
    {
        $template = $this->productsSelectionTemplateFactory->createFromEvent(
            $create->event,
            $create->title,
            $this->createdAt
        );

        $this->productsSelectionTemplateRepository->add($template);

        return $template;
    }
}
