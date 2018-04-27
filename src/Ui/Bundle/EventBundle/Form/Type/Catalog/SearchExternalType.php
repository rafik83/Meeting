<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
