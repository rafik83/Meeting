<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class TemplateDataFactory
{
    private $objects = [
        'button-link'   => Object\ButtonLink::class,
        'choice'        => Object::class,
        'collection'    => Object::class,
        'editable-text' => Object\EditableText::class,
        'image'         => Object::class,
        'media'         => Object::class,
        'nomenclature'  => Object\Nomenclature::class,
        'participant'   => Object::class,
        'tag'           => Object::class,
        'text'          => Object\Text::class,
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
     * @param Type   $type
     * @param string $locale
     *
     * @return TemplateData
     */
    public function createRegistrationFromType(Type $type, $locale)
    {
        return $this->create($type->getRegistrationTemplate()->getValue(), [], $locale);
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return TemplateData
     */
    public function createRegistrationFromParticipant(Participant $participant, $locale)
    {
        return $this->create(
            $participant->getSheet()->getType()->getRegistrationTemplate()->getValue(),
            $participant->getData(),
            $locale
        );
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
        $templateData = new TemplateData('root', 'root', []);

        foreach ($this->doCreate($template) as $name => $child) {
            $templateData->addChild(0, $name, $child);
        }

        foreach ($data as $key => $value) {
            $templateData->getObject($key)->setData($value);
        }

        foreach ($templateData->getObjects() as $object) {
            $object->setLocale($locale);
        }

        return $templateData;
    }

    /**
     * @param array  $config
     * @param string $objectKey
     *
     * @return array|Block
     * @throws \Exception
     */
    private function doCreate(array $config, $objectKey = null)
    {
        if (!isset($config['component'])) {
            return array_map(function (array $child) {
                return $this->doCreate($child);
            }, $config);
        }

        if ($config['component'] === 'block') {
            $block = new Block($objectKey, $config['type'], $config['config']);

            foreach ($config['children'] as $column => $children) {
                foreach ($children as $key => $child) {
                    $child = $this->doCreate($child, $key);
                    $block->addChild($column, $key, $child);
                }
            }

            return $block;
        }

        if ('object' === $config['component']) {
            $class  = $this->objects[$config['type']];
            $object = new $class($objectKey, $config['type'], $config['config']);

            return $object;
        }

        throw new \Exception();
    }
}
