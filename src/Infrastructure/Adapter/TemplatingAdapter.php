<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;

class TemplatingAdapter implements TemplatingAdapterInterface
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, array $context)
    {
        $template = $this->twig->loadTemplate($template);

        return $template->render($context);
    }
}
