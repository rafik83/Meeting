<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;

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
     * @param SheetTemplate $template
     *
     * @return array
     */
    public function resolve(SheetTemplate $template): array
    {
        $printValue = $template->getPrintValue();

        if (null === $printValue) {
            return $template->getValue();
        }

        $sheetTemplate = $this->templateDataFactory->createFromTemplate($template);
        $sheetTemplateObjects = $sheetTemplate->getObjects();

        return $this->replaceObjects($printValue, $sheetTemplateObjects);
    }

    /**
     * @param array $nodes
     * @param array $sheetTemplateObjects
     *
     * @return array
     */
    private function replaceObjects(array $nodes, array &$sheetTemplateObjects): array
    {
        foreach ($nodes as $key => $node) {
            if (!isset($node['component'])) {
                continue;
            }

            if ($node['component'] === 'block' && isset($node['children']) && is_array($node['children'])) {
                foreach ($node['children'] as $index => $children) {
                    $nodes[$key]['children'][$index] = $this->replaceObjects($children, $sheetTemplateObjects);
                }
            }

            if ($node['component'] === 'object') {
                if (!array_key_exists($key, $sheetTemplateObjects)) {
                    unset($nodes[$key]);
                    continue;
                }

                $nodes[$key] = $sheetTemplateObjects[$key]->normalize();
            }
        }

        return $nodes;
    }
}
