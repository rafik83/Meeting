<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\AbstractTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;

class TemplateDataFactory
{
    private $objects = [
        'button-link'           => Object\ButtonLink::class,
        'choice'                => Object::class,
        'collection'            => Object\ItemCollection::class,
        'editable-text'         => Object\EditableText::class,
        'image'                 => Object\Image::class,
        'media'                 => Object\MediaCollection::class,
        'nomenclature'          => Object\Nomenclature::class,
        'participant'           => Object::class,
        'tag'                   => Object::class,
        'text'                  => Object\Text::class,
        'carousel'              => Object::class,
        'telephone'             => Object\Telephone::class,
        'country'               => Object\Country::class,
        'url'                   => Object\Url::class,
        'package'               => Object::class,
        'participants_planings' => Object::class,
        'options'               => Object::class,
    ];

    /**
     * @var NomenclatureRepositoryInterface
     */
    private $nomenclatureRepository;

    /**
     * @var Nomenclature[]
     */
    private $nomenclatures = [];

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
        return $this
            ->loadNomenclatures($sheet->getEvent())
            ->create(
                $sheet->getType()->getSheetTemplate()->getValue(),
                $sheet->getData(),
                $locale,
                $sheet->getType()->getSheetTemplate()->getFallback()
            );
    }

    /**
     * @param Type   $type
     * @param string $locale
     *
     * @return TemplateData
     */
    public function createRegistrationFromType(Type $type, $locale)
    {
        return $this
            ->loadNomenclatures($type->getEvent())
            ->create(
                $type->getRegistrationTemplate()->getValue(),
                [],
                $locale,
                $type->getRegistrationTemplate()->getFallback()
            );
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return TemplateData
     */
    public function createRegistrationFromSheet(Sheet $sheet, $locale)
    {
        return $this
            ->loadNomenclatures($sheet->getEvent())
            ->create(
                $sheet->getType()->getRegistrationTemplate()->getValue(),
                $sheet->getRegistrationData(),
                $locale,
                $sheet->getType()->getRegistrationTemplate()->getFallback()
            );
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return TemplateData
     */
    public function createRegistrationFromParticipant(Participant $participant, $locale)
    {
        $datas = array_merge($participant->getData(), $participant->getSheet()->getRegistrationData());

        return $this
            ->loadNomenclatures($participant->getSheet()->getEvent())
            ->create(
                $participant->getSheet()->getType()->getRegistrationTemplate()->getValue(),
                $datas,
                $locale,
                $participant->getSheet()->getType()->getRegistrationTemplate()->getFallback()
            );
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return TemplateData
     */
    public function createProfileTemplate(Participant $participant, $locale)
    {
        return $this
            ->loadNomenclatures($participant->getSheet()->getEvent())
            ->create(
                $participant->getSheet()->getType()->getRegistrationTemplate()->getValue(),
                $participant->getData(),
                $locale,
                $participant->getSheet()->getType()->getRegistrationTemplate()->getFallback()
            );
    }

    /**
     * @param AbstractTemplate $template
     * @param array            $data
     * @param string|null      $locale
     * @param string|null      $fallback
     *
     * @return TemplateData
     */
    public function createFromTemplate(AbstractTemplate $template, array $data = [], $locale = null, $fallback = null)
    {
        return $this
            ->loadNomenclatures($template->getEvent())
            ->create($template->getValue(), $data, $locale, $fallback);
    }

    /**
     * @param array  $template
     * @param array  $data
     * @param string $locale
     * @param string $fallback
     *
     * @return TemplateData
     * @throws \Exception
     */
    public function create(array $template, array $data = [], $locale = null, $fallback = null)
    {
        $templateData = new TemplateData('root', [], $locale, $fallback);

        foreach ($this->doCreate($template, $locale, $fallback) as $name => $child) {
            $templateData->addChild(0, $name, $child);
        }

        foreach ($data as $key => $value) {
            try {
                $templateData->getObject($key)->setData($value ? : []);
            } catch (ObjectNotFoundException $exception) {
                // Don't try to set data if object not found
            }
        }

        return $templateData;
    }

    /**
     * @param array  $config
     * @param string $locale
     * @param string $fallback
     *
     * @return array|Block
     * @throws \Exception
     */
    private function doCreate(array $config, $locale, $fallback)
    {
        if (!isset($config['component'])) {
            return $this->buildComponents($config, $locale, $fallback);
        }

        if ($config['component'] === 'block') {
            return $this->buildBlock($config, $locale, $fallback);
        }

        if ('object' === $config['component']) {
            return $this->buildObject($config, $locale, $fallback);
        }

        throw new \Exception();
    }

    /**
     * @param array  $config
     * @param string $locale
     * @param string $fallback
     *
     * @return array
     */
    private function buildComponents(array $config, $locale, $fallback)
    {
        return array_map(
            function (array $child) use ($locale, $fallback) {
                return $this->doCreate($child, $locale, $fallback);
            }, $config
        );
    }

    /**
     * @param array  $config
     * @param string $locale
     * @param string $fallback
     *
     * @return Block
     * @throws \Exception
     */
    private function buildBlock(array $config, $locale, $fallback)
    {
        $block = new Block($config['type'], $config['config'], $locale, $fallback);

        foreach ($config['children'] as $column => $children) {
            $block->addColumn($column);
            foreach ($children as $key => $child) {
                $child = $this->doCreate($child, $locale, $fallback);
                $block->addChild($column, $key, $child);
            }
        }

        return $block;
    }

    /**
     * @param array  $config
     * @param string $locale
     * @param string $fallback
     *
     * @return mixed
     */
    private function buildObject(array $config, $locale, $fallback)
    {
        $class  = $this->objects[$config['type']];
        $object = new $class($config['type'], $config['config'], $locale, $fallback);

        if ($object instanceof Object\Nomenclature) {
            if ($object->getNomenclatureId()) {
                $object->setNomenclature($this->getNomenclature($object->getNomenclatureId()));
            }
        }

        return $object;
    }

    /**
     * @param int $id
     *
     * @return Nomenclature
     */
    private function getNomenclature($id)
    {
        if (!isset($this->nomenclatures[$id])) {
            throw new \RuntimeException(sprintf('Nomenclature "%s" not found. Available nomenclatures are "%s"', $id, implode('", "', array_keys($this->nomenclatures))));
        }

        return $this->nomenclatures[$id];
    }

    /**
     * @param Event $event
     *
     * @return TemplateDataFactory
     */
    private function loadNomenclatures(Event $event = null)
    {
        $this->nomenclatures = $event
            ? $this->nomenclatureRepository->findByEvent($event)
            : $this->nomenclatureRepository->findGlobals();

        return $this;
    }
}
