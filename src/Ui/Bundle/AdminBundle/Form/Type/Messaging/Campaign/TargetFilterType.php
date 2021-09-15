<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign;

use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\AbstractFilterType;

/**
 * Filter form used to select sheets of an event as targets of a messaging campaign (emailing).
 */
class TargetFilterType extends AbstractFilterType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'targetting';
    }
}
