<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Twig\Extension;

use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Twig\Util\AttributeBag;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Twig\Util\TooltipBag;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Twig\Util\PopoverBag;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Twig\Util\CollapseBag;
use Twig\Environment;
use Twig\Extension\AbstractExtension;

/**
 * Theme Twitter Bootstrap3 Twig Extension
 */
class TwitterBootstrap3Extension extends AbstractExtension
{
    /**
     * @var Environment
     */
    private $templating;

    /**
     * @param Environment $templating
     */
    public function __construct(Environment $templating)
    {
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'vimeet_theme_twitter_bootstrap_3_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('bool_to_str', array($this, 'booleanToString')),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('tooltip', array($this, 'tooltip'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('popover', array($this, 'popover'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('collapse', array($this, 'collapse'), array('is_safe' => array('html'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return array();
    }

    /**
     * Tooltip
     *
     * @param array $parameters
     *
     * @return string
     */
    public function tooltip($parameters = array())
    {
        return $this->renderAttributes(new TooltipBag($parameters));
    }

    /**
     * Popover
     *
     * @param array $parameters
     *
     * @return string
     */
    public function popover($parameters = array())
    {
        return $this->renderAttributes(new PopoverBag($parameters));
    }

    /**
     * Collapse
     *
     * @param array $parameters
     *
     * @return string
     */
    public function collapse($parameters = array())
    {
        return $this->renderAttributes(new CollapseBag($parameters));
    }

    /**
     * Render attributes
     *
     * @param AttributeBag $bag
     *
     * @return string
     */
    protected function renderAttributes(AttributeBag $bag)
    {
        return $this->templating->render(
            '@Infrastructure/Block/data_attributes.html.twig',
            array('attributes' => $bag->getAttributes())
        );
    }

    /**
     * Give the string version of a boolean (for Twitter Boostrap 3 javascript config)
     *
     * @param boolean $value
     *
     * @return string
     */
    public function booleanToString($value = null)
    {
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        return $value;
    }
}
