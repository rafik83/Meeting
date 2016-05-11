<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class TemplateDataFactory
{
    private $objects = [
        'button-link'   => Object\ButtonLink::class,
        'choice'        => Object::class,
        'collection'    => Object\ItemCollection::class,
        'editable-text' => Object\EditableText::class,
        'image'         => Object\Image::class,
        'media'         => Object\MediaCollection::class,
        'nomenclature'  => Object\Nomenclature::class,
        'participant'   => Object::class,
        'tag'           => Object::class,
        'text'          => Object\Text::class,
        'carousel'      => Object::class,
        'telephone'     => Object\Telephone::class,
        'country'       => Object\Country::class,
        'url'           => Object\Url::class,
    ];

    /**
     * @var NomenclatureRepositoryInterface
     */
    private $nomenclatureRepository;

    /**
     * @var Nomenclature[]
     */
    private $nomenclatures;

    /**
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     */
    public function __construct(NomenclatureRepositoryInterface $nomenclatureRepository)
    {
        $this->nomenclatureRepository = $nomenclatureRepository;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return TemplateData
     */
    public function createFromSheet(Sheet $sheet, $locale)
    {
        $this->nomenclatures = $this->nomenclatureRepository->findByEvent($sheet->getEvent());

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
        $this->nomenclatures = $this->nomenclatureRepository->findByEvent($type->getEvent());

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
        $this->nomenclatures = $this->nomenclatureRepository->findByEvent($participant->getSheet()->getEvent());

        $datas = array_merge($participant->getData(), $participant->getSheet()->getRegistrationData());

        return $this->create(
            $participant->getSheet()->getType()->getRegistrationTemplate()->getValue(),
            $datas,
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
        $templateData = new TemplateData('root', []);

        foreach ($this->doCreate($template, $locale) as $name => $child) {
            $templateData->addChild(0, $name, $child);
        }

        foreach ($data as $key => $value) {
            $templateData->getObject($key)->setData($value);
        }

        foreach ($templateData->getObjects() as $object) {
            if ($object instanceof Object\Nomenclature && null !== $this->nomenclatures && isset($this->nomenclatures[$object->getNomenclatureId()])) {
                $object->setNomenclatureLabels($this->nomenclatures[$object->getNomenclatureId()]->getLabels($object->getLocale()) ? : []);
            }
        }

        return $templateData;
    }

    /**
     * @param array  $config
     * @param string $locale
     *
     * @return array|Block
     * @throws \Exception
     */
    private function doCreate(array $config, $locale)
    {
        if (!isset($config['component'])) {
            return array_map(function (array $child) use ($locale) {
                return $this->doCreate($child, $locale);
            }, $config);
        }

        if ($config['component'] === 'block') {
            $block = new Block($config['type'], $config['config']);

            foreach ($config['children'] as $column => $children) {
                foreach ($children as $key => $child) {
                    $child = $this->doCreate($child, $locale);
                    $block->addChild($column, $key, $child);
                }
            }

            return $block;
        }

        if ('object' === $config['component']) {
            $class  = $this->objects[$config['type']];
            $object = new $class($config['type'], $config['config'], $locale);

            return $object;
        }

        throw new \Exception();
    }
}
