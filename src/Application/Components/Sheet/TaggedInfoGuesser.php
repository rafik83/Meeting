<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Template\Exception\RowNotFoundException;
use Proximum\Vimeet\Application\Components\Template\TemplateFactory;

class TaggedInfoGuesser
{
    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * TaggedInfoGuesser constructor.
     *
     * @param TemplateFactory $templateFactory
     */
    public function __construct(TemplateFactory $templateFactory)
    {
        $this->templateFactory = $templateFactory;
    }

    /**
     * @param array  $template
     * @param array  $data
     * @param string $tag
     * @param string $locale
     *
     * @return array
     */
    public function guess(array $template, array $data, $tag, $locale = null)
    {
        $values = array_values($template);
        $first  = reset($values);

        if (!isset($first['template'])) {
            $template = ['default' => ['label' => 'Default', 'template' => $template]];
            $data     = ['default' => $data];
        }

        return $this->templateFactory->createTemplatesFromArray($template)->getTaggedValues($tag, $locale, $data);
    }

    /**
     * @param array      $template
     * @param array      $data
     * @param string     $tag
     * @param string     $locale
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function guessFirst(array $template, array $data, $tag, $locale = null, $default = null)
    {
        try {
            $info = $this->guess($template, $data, $tag, $locale);

            return !empty($info) ? reset($info) : $default;
        } catch (RowNotFoundException $exception) {
            return $default;
        }
    }
}
