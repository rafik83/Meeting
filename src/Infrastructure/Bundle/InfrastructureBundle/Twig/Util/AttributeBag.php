<?php


namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Twig\Util;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * HTML Attributes Bag
 */
abstract class AttributeBag
{
    /**
     * HTML Attributes
     *
     * @var array
     */
    protected $attributes;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->resolveOptions($options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    abstract protected function setDefaultOptions(OptionsResolver $resolver);

    /**
     * Build the attributes based on given options
     *
     * @param array $options
     */
    private function resolveOptions($options = array())
    {
        $resolver = new OptionsResolver();

        $this->setDefaultOptions($resolver);

        $this->attributes = $resolver->resolve($options);
    }

    /**
     * Get resolved attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
