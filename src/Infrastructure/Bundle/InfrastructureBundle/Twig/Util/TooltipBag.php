<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Twig\Util;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Tooltip HTML Attributes Bag
 */
class TooltipBag extends AttributeBag
{
    /**
     * {@inheritdoc}
     */
    protected function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['toggle' => 'tooltip']);

        $resolver->setDefined([
            'animation',
            'html',
            'placement',
            'selector',
            'title',
            'trigger',
            'delay',
            'container',
        ]);

        $resolver->setAllowedTypes('animation', 'bool');
        $resolver->setAllowedTypes('html', 'bool');
        $resolver->setAllowedTypes('placement', 'string');
        $resolver->setAllowedTypes('selector', array('string', 'bool'));
        $resolver->setAllowedTypes('title', 'string');
        $resolver->setAllowedTypes('container', array('string', 'bool'));

        $resolver->setAllowedValues('placement', array('top', 'bottom', 'left', 'right', 'auto'));
        $resolver->setAllowedValues('trigger', array('click', 'hover', 'focus', 'manual'));
    }
}
