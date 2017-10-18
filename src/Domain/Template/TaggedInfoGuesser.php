<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Model\Template\AbstractTemplate;

class TaggedInfoGuesser
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param AbstractTemplate $template
     * @param array            $data
     * @param string           $tag
     * @param string           $locale
     *
     * @return array
     */
    public function guess(AbstractTemplate $template, $data, $tag, $locale)
    {
        $templateData = $this->templateDataFactory->create(
            $template->getValue(),
            $data,
            $locale,
            $template->getFallback()
        );

        return $templateData->getTaggedDatas($tag);
    }

    /**
     * @param AbstractTemplate $template
     * @param array            $data
     * @param string           $tag
     * @param string           $locale
     * @param string|null      $default
     *
     * @return string|bool|null
     */
    public function guessFirst(AbstractTemplate $template, $data, $tag, $locale, $default = null)
    {
        $templateData = $this->templateDataFactory->createFromTemplate(
            $template,
            $data,
            $locale,
            $template->getFallback()
        );

        return $this->guessFirstFromTemplateData($templateData, $tag, $default);
    }

    /**
     * @param TemplateData $templateData
     * @param string       $tag
     * @param string|null  $default
     *
     * @return string|bool|null
     */
    public function guessFirstFromTemplateData(TemplateData $templateData, $tag, $default = null)
    {
        $taggedDatas = $templateData->getTaggedDatas($tag);
        $taggedData  = reset($taggedDatas);

        return $taggedData !== null ? $taggedData : $default;
    }
}
