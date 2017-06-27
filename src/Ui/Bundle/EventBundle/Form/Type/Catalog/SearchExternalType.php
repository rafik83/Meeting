<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog;

class SearchExternalType extends AbstractSearchType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'catalog_external_search';
    }
}
