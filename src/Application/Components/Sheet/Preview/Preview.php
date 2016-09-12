<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Preview;

use Proximum\Vimeet\Application\View\Sheet\Preview\PreviewView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Rule\ComposedRule;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class Preview
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
     * @param Sheet             $sheet
     * @param string            $locale
     * @param ComposedRule|null $composedRule
     *
     * @return TemplateObject[]
     */
    public function getPreview(Sheet $sheet, $locale, ComposedRule $composedRule = null)
    {
        $previewObjectKeys = $sheet->getTypeSheetTemplate()->getPreview();
        $templateData      = $this->templateDataFactory->createFromSheet($sheet, $locale);
        $taggedData        = $this->templateDataFactory->createRegistrationFromSheet($sheet, $locale)->getAllTaggedDatas();

        $previewObjects = [];

        foreach ($previewObjectKeys as $key) {
            $object      = $templateData->getObject($key);
            $previewView = new PreviewView($object->getKey(), '', $object->getType());

            if ($object instanceof TemplateObject\ContentObjectInterface) {
                if ($object instanceof TemplateObject\EditableText && $object->isTitle()) {
                    $previewView->strong = true;
                }

                if ($object->getContentValue() === ''
                    && $object->getTag() !== null
                    && isset($taggedData[$object->getTag()])
                    && !empty($taggedData[$object->getTag()])
                    && $this->isTagVisible($object->getTag(), $composedRule)
                ) {
                    $previewView->content = reset($taggedData[$object->getTag()]);
                } else {
                    $previewView->content = $object->getContentValue();
                }
            }

            $previewObjects[] = $previewView;
        }

        return $previewObjects;
    }

    private function isTagVisible($tag, ComposedRule $composedRule = null)
    {
        if (null === $composedRule) {
            return true;
        }

        return in_array($tag, $composedRule->tags);
    }
}
