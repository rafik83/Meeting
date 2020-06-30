<?php


namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Twig\Util;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Popover HTML Attributes Bag
 */
class PopoverBag extends TooltipBag
{
    /**
     * {@inheritdoc}
     */
    protected function setDefaultOptions(OptionsResolver $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefault('toggle', 'popover');
        $resolver->setDefined(array('content'));
        $resolver->setAllowedTypes('content', 'string');
    }
}
