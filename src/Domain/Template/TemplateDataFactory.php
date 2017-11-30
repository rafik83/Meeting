<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Application\Components\Sheet\Preview\CustomPreviewData;
use Proximum\Vimeet\Domain\Exception\Nomenclature\NomenclatureNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\AbstractTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\BuildNotImplementedException;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;

class TemplateDataFactory
{
    private $objects = [
        'button-link'           => TemplateObject\ButtonLink::class,
        'choice'                => TemplateObject::class,
        'collection'            => TemplateObject\ItemCollection::class,
        'editable-text'         => TemplateObject\EditableText::class,
        'image'                 => TemplateObject\Image::class,
        'media'                 => TemplateObject\MediaCollection::class,
        'nomenclature'          => TemplateObject\Nomenclature::class,
        'participant'           => TemplateObject\Participant::class,
        'tag'                   => TemplateObject\Tag::class,
        'text'                  => TemplateObject\Text::class,
        'carousel'              => TemplateObject::class,
        'telephone'             => TemplateObject\Telephone::class,
        'country'               => TemplateObject\Country::class,
        'url'                   => TemplateObject\Url::class,
        'package'               => TemplateObject::class,
        'participants_planings' => TemplateObject::class,
        'options'               => TemplateObject::class,
        'tags'                  => TemplateObject\TagsCollection::class,
        'gender'                => TemplateObject\Gender::class,
        'boolean'               => TemplateObject\BooleanObject::class,
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
     * Cached nomenclatures by event to avoid multiples request
     *
     * Array by id of events of nomenclature
     *
     * @var array
     */
    private $nomenclatureByEvent = [];

    /**
     * Cached global nomenclatures
     *
     * null or array of nomenclatures
     *
     * @var null|array
     */
    private $globalsNomenclatures = null;

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
     * @param Sheet       $sheet
     * @param string|null $locale
     *
     * @return TemplateData
     */
    public function createRegistrationFromSheet(Sheet $sheet, $locale = null)
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
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return TemplateData
     */
    public function createCompanyTemplate(Sheet $sheet, $locale)
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
     */
    public function create(array $template, array $data = [], $locale = null, $fallback = null)
    {
        $templateData = new TemplateData('root', [], $locale, $fallback);

        foreach ($this->doCreate($template, $locale, $fallback) as $name => $child) {
            $templateData->addChild(0, $name, $child);
        }

        foreach ($data as $key => $value) {
            try {
                $templateObject = $templateData->getObject($key);
                $templateObject->setData($value ?: []);

                if ($templateObject instanceof EditableText
                    && empty($templateObject->getContentValueLocalize($locale))
                    && $templateObject->isTranslatable()
                ) {
                    $templateObject->setContent($this->getFirstNotEmptyContent($templateObject));
                }

            } catch (ObjectNotFoundException $exception) {
                // Don't try to set data if object not found
            }
        }

        return $templateData;
    }

    /**
     * @param TemplateObject $templateObject
     *
     * @return string|null
     */
    private function getFirstNotEmptyContent(TemplateObject $templateObject):? string
    {
        $translations = $templateObject->getTranslations();

        foreach ($translations as $translation) {
            if (!empty($translation)) {
                return $translation;
            }
        }

        return null;
    }

    /**
     * @param TemplateData $templateData
     *
     * @return array of TemplateObject and CustomPreviewDataView
     */
    public function getPreviewAvailableData(TemplateData $templateData): array
    {
        return array_merge($templateData->getPreviewAvailableObjects(), CustomPreviewData::getCustomPreviewDataViews());
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

        throw new BuildNotImplementedException('config given is not a block nor an object');
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
        return array_combine(array_keys($config), array_map(
            function (array $child, $key) use ($locale, $fallback) {
                $child['key'] = $key;

                return $this->doCreate($child, $locale, $fallback);
            }, $config, array_keys($config)
        ));
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
                $child['key'] = $key;
                $child        = $this->doCreate($child, $locale, $fallback);
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
     *
     * @throws NomenclatureNotFoundException
     */
    private function buildObject(array $config, $locale, $fallback)
    {
        $class  = $this->objects[$config['type']];
        $object = new $class($config['key'], $config['type'], $config['config'], $locale, $fallback);

        if ($object instanceof TemplateObject\Nomenclature) {
            if ($object->getNomenclatureId() === '') {
                throw new NomenclatureNotFoundException();
            }

            if ($object->getNomenclatureId()) {
                $object->setNomenclature($this->getNomenclature(intval($object->getNomenclatureId())));
            }
        }

        return $object;
    }

    /**
     * @param int $id
     *
     * @return Nomenclature
     *
     * @throws NomenclatureNotFoundException
     */
    private function getNomenclature($id)
    {
        if (!isset($this->nomenclatures[$id])) {
            throw new NomenclatureNotFoundException(
                sprintf(
                    'Nomenclature "%s" not found. Available nomenclatures are "%s"',
                    $id,
                    implode('", "', array_keys($this->nomenclatures))
                )
            );
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
            ? $this->findNomenclatureByEvent($event)
            : $this->getGlobalsNomenclatures();

        return $this;
    }

    /**
     * @param Event $event
     *
     * @return Nomenclature[]
     */
    private function findNomenclatureByEvent(Event $event)
    {
        if (!isset($this->nomenclatureByEvent[$event->getId()])) {
            $this->nomenclatureByEvent[$event->getId()] = $this->nomenclatureRepository->findByEvent($event);
        }

        return $this->nomenclatureByEvent[$event->getId()];
    }

    /**
     * @return Nomenclature[]
     */
    private function getGlobalsNomenclatures()
    {
        if (!$this->globalsNomenclatures) {
            $this->globalsNomenclatures = $this->nomenclatureRepository->findGlobals();
        }

        return $this->globalsNomenclatures;
    }
}
