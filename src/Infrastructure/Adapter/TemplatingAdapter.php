<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;
use Twig\Environment;

class TemplatingAdapter implements TemplatingAdapterInterface
{
    private Environment $twig;

    public function __construct(Environment $twig)
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
