<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Extension;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractTypeExtension as Base;

abstract class AbstractTypeExtension extends Base
{
    /**
     * Add block prefix
     *
     * @param FormView $view
     * @param string $blockPrefix
     */
    protected function addBlockPrefix(FormView $view, $blockPrefix)
    {
        if (!in_array($blockPrefix, $view->vars['block_prefixes'])) {
            $view->vars['block_prefixes'][] = $blockPrefix;
        }
    }
}
