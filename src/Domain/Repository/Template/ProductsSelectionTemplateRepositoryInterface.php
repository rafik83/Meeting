<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Template;

use Proximum\Vimeet\Domain\Model\Template\ProductsSelectionTemplate;

interface ProductsSelectionTemplateRepositoryInterface
{
    /**
     * @param array $events
     *
     * @return ProductsSelectionTemplate[]
     */
    public function getTemplateForGivenEvents(array $events);

    /**
     * @param ProductsSelectionTemplate $productsSelectionTemplate
     */
    public function add(ProductsSelectionTemplate $productsSelectionTemplate);
}
