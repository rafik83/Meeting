<?php


namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Twig\Util;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Collapse HTML Attributes Bag
 */
class CollapseBag extends AttributeBag
{
    /**
     * {@inheritdoc}
     */
    protected function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['toggle' => 'collapse']);
        $resolver->setRequired(['parent']);
        $resolver->setDefined(['target']);
        $resolver->setAllowedTypes('toggle', 'string');
        $resolver->setAllowedTypes('parent', 'string');
        $resolver->setAllowedTypes('target', 'string');
    }
}
