<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Model\Sheet;

class TemplateDataFactory
{
    private $objects = [
        'button-link'   => Object\ButtonLink::class,
        'choice'        => Object::class,
        'collection'    => Object\ItemCollection::class,
        'editable-text' => Object\EditableText::class,
        'image'         => Object::class,
        'media'         => Object\MediaCollection::class,
        'nomenclature'  => Object::class,
        'participant'   => Object::class,
        'tag'           => Object::class,
        'text'          => Object::class,
        'carousel'      => Object::class,
    ];

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return TemplateData
     */
    public function createFromSheet(Sheet $sheet, $locale)
    {
        return $this->create($sheet->getType()->getNewSheetTemplate()->getValue(), $sheet->getData(), $locale);
    }

    /**
     * @param array  $template
     * @param array  $data
     * @param string $locale
     *
     * @return TemplateData
     * @throws \Exception
     */
    public function create(array $template, array $data, $locale)
    {
        $templateData = new TemplateData('root', []);

        foreach ($this->doCreate($template) as $name => $child) {
            $templateData->addChild(0, $name, $child);
        }

        foreach ($data as $key => $value) {
            $templateData->getObject($key)->setData($value)->setLocale($locale);
        }

        return $templateData;
    }

    /**
     * @param array $config
     *
     * @return array|Block
     * @throws \Exception
     */
    private function doCreate(array $config)
    {
        if (!isset($config['component'])) {
            return array_map(function (array $child) {
                return $this->doCreate($child);
            }, $config);
        }

        if ($config['component'] === 'block') {

            $block = new Block($config['type'], $config['config']);

            foreach ($config['children'] as $column => $children) {
                foreach ($this->doCreate($children) as $key => $child) {
                    $block->addChild($column, $key, $child);
                }
            }

            return $block;
        }

        if ($config['component'] === 'object') {

            $class  = $this->objects[$config['type']];
            $object = new $class($config['type'], $config['config']);

            return $object;
        }

        throw new \Exception();
    }
}
