<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\View\Template\TaggedDataView;

class TaggedDataFactory
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var TaggedDataView[]
     */
    private $taggedDataViews = [];

    /**
     * TaggedDataFactory constructor.
     *
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * Get tagged data from registration template and build TaggedDataView into sheetTemplate
     *
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return TemplateData sheetTemplate
     */
    public function buildTaggedDataView(Sheet $sheet, $locale)
    {
        $this->createTaggedDataView($sheet, $locale);
        $sheetTemplateData = $this->attachTaggedDataView($sheet, $locale);

        return $sheetTemplateData;
    }

    /**
     * Build taggedDataView for all registration template objects
     *
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @see TaggedDataView
     */
    private function createTaggedDataView(Sheet $sheet, $locale)
    {
        $registerTemplateData = $this->templateDataFactory->createRegistrationFromSheet($sheet, $locale);

        $objects      = $registerTemplateData->getContentObjects();
        $eventLocales = $sheet->getEvent()->getLocales();

        /** @var TemplateObject|ContentObjectInterface $object */
        foreach ($objects as $object) {
            $tags = $object->getTags();

            if (count($tags) === 0) {
                continue;
            }

            // Filter only the object with SHEET_DATA setter,
            // as they are the only one that can be display on the sheet
            if (!in_array(Tag::SHEET_DATA, $tags)) {
                continue;
            }

            foreach ($tags as $tag) {
                if (in_array($tag, Tag::getSetters())) {
                    continue;
                }

                if ($object instanceof TemplateObject\Nomenclature) {
                    $value = $object->getNomenclatureLabel();
                } else {
                    $value = $object->getContentValue();
                }

                $taggedDataView = new TaggedDataView(
                    $object->getType(),
                    $object->isTranslatable(),
                    $object instanceof TranslatableInterface ? $object->getTranslations($eventLocales) : [],
                    $value,
                    $tag,
                    $object instanceof EditableText ? $object->isTextarea() : false
                );

                $this->addTaggedDataView($tag, $taggedDataView);
            }
        }
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return TemplateData
     */
    private function attachTaggedDataView(Sheet $sheet, $locale)
    {
        $sheetTemplateData = $this->templateDataFactory->createFromSheet($sheet, $locale);

        $sheetTemplateData->setTaggedDataViews($this->taggedDataViews);

        return $sheetTemplateData;
    }

    /**
     * @param string         $tag
     * @param TaggedDataView $taggedDataView
     */
    private function addTaggedDataView($tag, $taggedDataView)
    {
        if (!isset($this->taggedDataViews[$tag])) {
            $this->taggedDataViews[$tag] = $taggedDataView;
        }
    }
}
