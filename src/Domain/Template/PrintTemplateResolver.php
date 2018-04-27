<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\View\Template\ResolvedPrintTemplateView;

class PrintTemplateResolver
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /**
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param SheetTemplate $sheetTemplate
     *
     * @return ResolvedPrintTemplateView
     */
    public function resolve(SheetTemplate $sheetTemplate): ResolvedPrintTemplateView
    {
        $printValue = $sheetTemplate->getPrintValue();

        if (empty($printValue)) {
            return new ResolvedPrintTemplateView($sheetTemplate->getValue(), []);
        }

        $sheetTemplateData        = $this->templateDataFactory->createFromTemplate($sheetTemplate);
        $sheetTemplateDataObjects = $sheetTemplateData->getObjects();

        $printValueResolved = $this->replaceObjects($printValue, $sheetTemplateDataObjects);
        $missingObjects = $this->getMissingObjects($printValueResolved, $sheetTemplateDataObjects);

        return new ResolvedPrintTemplateView($printValueResolved, $missingObjects);
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return TemplateData
     */
    public function resolvePrintTemplate(Sheet $sheet, string $locale)
    {
        $sheetTemplate = $sheet->getTypeSheetTemplate();
        $printValue = $sheet->getTypeSheetTemplate()->getPrintValue();

        if (empty($printValue)) {
            return $this->templateDataFactory->createFromTemplate(
                $sheetTemplate,
                $sheet->getData(),
                $locale,
                $sheetTemplate->getFallback()
            );
        }

        $sheetTemplateData        = $this->templateDataFactory->createFromTemplate($sheetTemplate);
        $sheetTemplateDataObjects = $sheetTemplateData->getObjects();
        $printTemplateNodes       = $this->replaceObjects($printValue, $sheetTemplateDataObjects);

        return $this->templateDataFactory->create(
            $printTemplateNodes,
            $sheet->getData(),
            $locale,
            $sheetTemplate->getFallback()
        );
    }

    /**
     * @param array $nodes
     * @param array $sheetTemplateDataObjects
     *
     * @return array
     */
    private function replaceObjects(array $nodes, array &$sheetTemplateDataObjects): array
    {
        foreach ($nodes as $key => $node) {
            if (!isset($node['component'])) {
                continue;
            }

            if ('block' === $node['component'] && isset($node['children']) && is_array($node['children'])) {
                foreach ($node['children'] as $index => $children) {
                    $nodes[$key]['children'][$index] = $this->replaceObjects($children, $sheetTemplateDataObjects);
                }
            }

            if ('object' === $node['component']) {
                if (!array_key_exists($key, $sheetTemplateDataObjects)) {
                    unset($nodes[$key]);
                    continue;
                }

                $nodes[$key] = $sheetTemplateDataObjects[$key]->normalize();
            }
        }

        return $nodes;
    }

    /**
     * @param array            $printValueResolved
     * @param TemplateObject[] $sheetTemplateDataObjects
     *
     * @return array
     */
    private function getMissingObjects(array $printValueResolved, array $sheetTemplateDataObjects): array
    {
        $missingObjects = [];
        $sheetPrintTemplateData        = $this->templateDataFactory->create($printValueResolved);
        $sheetPrintTemplateDataObjects = $sheetPrintTemplateData->getObjects();

        foreach ($sheetTemplateDataObjects as $key => $templateObject) {
            if (!array_key_exists($key, $sheetPrintTemplateDataObjects)) {
                $missingObjects[$key] = $templateObject->normalize();
            }
        }

        return $missingObjects;
    }
}
