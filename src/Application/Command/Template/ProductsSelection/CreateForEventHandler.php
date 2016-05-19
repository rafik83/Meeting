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

class CreateForEventHandler
{
    /**
     * @var ProductsSelectionTemplateRepositoryInterface
     */
    private $productsSelectionTemplateRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * CreateForEventHandler constructor.
     *
     * @param ProductsSelectionTemplateRepositoryInterface $productsSelectionTemplateRepository
     * @param \DateTimeInterface                           $dateTime
     */
    public function __construct(
        ProductsSelectionTemplateRepositoryInterface $productsSelectionTemplateRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->productsSelectionTemplateRepository = $productsSelectionTemplateRepository;
        $this->dateTime                            = $dateTime;
    }

    /**
     * @param CreateForEvent $create
     */
    public function handle(CreateForEvent $create)
    {
        $template = new ProductsSelectionTemplate(
            $create->title,
            [],
            $create->event->getLocales(),
            $create->event->getFallback(),
            $this->dateTime
        );

        $template->setEvent($create->event);

        $this->productsSelectionTemplateRepository->add($template);
    }
}
