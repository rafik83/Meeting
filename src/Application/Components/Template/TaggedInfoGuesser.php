<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template;

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
        try {
            $values = array_values($template);
            $first  = reset($values);

            return isset($first['template'])
                ? $this->templateFactory->createTemplatesFromArray($template)->getTaggedValues($tag, $locale, $data)
                : $this->templateFactory->createTemplateFromArray($template)->getTaggedValues($tag, $locale, $data);
        } catch (\Exception $exception) {
            return null;
        }
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
            $values = array_values($template);
            $first  = reset($values);

            $value = isset($first['template'])
                ? $this->templateFactory->createTemplatesFromArray($template)->getTaggedValue($tag, $locale, $data)
                : $this->templateFactory->createTemplateFromArray($template)->getTaggedValue($tag, $locale, $data);
        } catch (\Exception $exception) {
            return null;
        }

        return $value !== null ? $value : $default;
    }
}
