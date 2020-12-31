<?php

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
