<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\BuildNotImplementedException;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContextEventInterface;
use Proximum\Vimeet\Domain\Template\Exception\ObjectsCollectionBlockCanNotContainForbiddenObjectsException;
use Proximum\Vimeet\Domain\Template\Exception\ObjectsCollectionBlockCanNotContainOtherBlockException;
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
        'upload'                => TemplateObject\UploadObject::class,
        'datetime'              => TemplateObject\DateTime::class,
        'multi-upload'          => TemplateObject\MultiUploadCollectionObject::class,
        'video'                 => TemplateObject\Video::class,
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
     * @param Sheet       $sheet
     * @param null|string $locale
     *
     * @return TemplateData
     */
    public function createFromSheet(Sheet $sheet, ?string $locale = null): TemplateData
    {
        $templateData = $this
            ->loadNomenclatures($sheet->getEvent())
            ->create(
                $sheet->getType()->getSheetTemplate()->getValue(),
                $sheet->getData(),
                $locale,
                $sheet->getType()->getSheetTemplate()->getFallback(),
                $sheet->getEvent()
            );

        foreach ($templateData->getObjects() as $templateObject) {
            $templateObject->setSheet($sheet);
        }

        return $templateData;
    }

    /**
     * @param RegistrationTemplate $registrationTemplate
     * @param null|string          $locale
     *
     * @return TemplateData
     */
    public function createRegistrationFromTemplate(
        RegistrationTemplate $registrationTemplate,
        ?string $locale
    ): TemplateData {
        return $this
            ->loadNomenclatures($registrationTemplate->getEvent())
            ->create(
                $registrationTemplate->getValue(),
                [],
                $locale,
                $registrationTemplate->getFallback(),
                $registrationTemplate->getEvent()
            )
            ;
    }

    public function createFormTemplateFromTemplate(
        FormTemplate $formTemplate,
        string $locale
    ): TemplateData {
        return $this
            ->loadNomenclatures($formTemplate->getEvent())
            ->create(
                $formTemplate->getValue(),
                [],
                $locale,
                $formTemplate->getFallback()
            )
            ;
    }

    public function createFormTemplateWithData(
        FormTemplate $formTemplate,
        array $data,
        string $locale
    ): TemplateData {
        return $this
            ->loadNomenclatures($formTemplate->getEvent())
            ->create(
                $formTemplate->getValue(),
                $data,
                $locale,
                $formTemplate->getFallback(),
                $formTemplate->getEvent()
            )
            ;
    }

    /**
     * @param Type        $type
     * @param null|string $locale
     *
     * @return TemplateData
     */
    public function createRegistrationFromType(Type $type, ?string $locale): TemplateData
    {
        return $this->createRegistrationFromTemplate($type->getRegistrationTemplate(), $locale);
    }

    /**
     * @param Type        $type
     * @param null|string $locale
     *
     * @return TemplateData
     */
    public function createSheetTemplateFromType(Type $type, ?string $locale = null): TemplateData
    {
        return $this->createFromTemplate($type->getSheetTemplate(), [], $locale);
    }

    /**
     * @param Sheet       $sheet
     * @param string|null $locale
     *
     * @return TemplateData
     */
    public function createRegistrationFromSheet(Sheet $sheet, ?string $locale = null): TemplateData
    {
        return $this
            ->loadNomenclatures($sheet->getEvent())
            ->create(
                $sheet->getType()->getRegistrationTemplate()->getValue(),
                $sheet->getRegistrationData(),
                $locale,
                $sheet->getType()->getRegistrationTemplate()->getFallback(),
                $sheet->getEvent()
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
                $participant->getSheet()->getType()->getRegistrationTemplate()->getFallback(),
                $participant->getSheet()->getEvent()
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
                $participant->getSheet()->getType()->getRegistrationTemplate()->getFallback(),
                $participant->getSheet()->getEvent()
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
                $sheet->getType()->getRegistrationTemplate()->getFallback(),
                $sheet->getEvent()
            );
    }

    /**
     * @param AbstractTemplate $template
     * @param array            $data
     * @param string|null      $locale
     * @param string|null      $fallback
     *
     * @return TemplateData
     *
     * @throws ObjectsCollectionBlockCanNotContainForbiddenObjectsException
     * @throws ObjectsCollectionBlockCanNotContainOtherBlockException
     * @throws \Exception
     */
    public function createFromTemplate(AbstractTemplate $template, array $data = [], $locale = null, $fallback = null)
    {
        return $this
            ->loadNomenclatures($template->getEvent())
            ->create(
                $template->getValue(),
                $data,
                $locale,
                $fallback,
                $template->getEvent()
            );
    }

    /**
     * @param array       $template
     * @param array       $data
     * @param null|string $locale
     * @param string      $fallback
     * @param null|Event  $event
     *
     * @return TemplateData
     *
     * @throws ObjectNotFoundException
     * @throws ObjectsCollectionBlockCanNotContainForbiddenObjectsException
     * @throws ObjectsCollectionBlockCanNotContainOtherBlockException
     * @throws \Exception
     */
    public function create(array $template, array $data = [], ?string $locale = null, $fallback = null, ?Event $event = null)
    {
        $templateData = new TemplateData('root', [], $locale, $fallback);

        foreach ($this->doCreate($template, $locale, $fallback, $event) as $name => $child) {
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
     * @return string|array|null
     */
    private function getFirstNotEmptyContent(TemplateObject $templateObject)
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
     * @param array         $config
     * @param string        $locale
     * @param string        $fallback
     * @param null|Event    $event
     *
     * @throws \Exception
     *
     * @return array|Block
     */
    private function doCreate(array $config, $locale, $fallback, ?Event $event = null)
    {
        if (!isset($config['component'])) {
            return $this->buildComponents($config, $locale, $fallback, $event);
        }

        if ('block' === $config['component']) {
            return $this->buildBlock($config, $locale, $fallback, $event);
        }

        if ('object' === $config['component']) {
            return $this->buildObject($config, $locale, $fallback, $event);
        }

        throw new BuildNotImplementedException('config given is not a block nor an object');
    }

    /**
     * @param array         $config
     * @param string        $locale
     * @param string        $fallback
     * @param null|Event    $event
     *
     * @return array
     */
    private function buildComponents(array $config, $locale, $fallback, ?Event $event = null)
    {
        return array_combine(array_keys($config), array_map(
            function (array $child, $key) use ($locale, $fallback, $event) {
                $child['key'] = $key;

                return $this->doCreate($child, $locale, $fallback, $event);
            }, $config, array_keys($config)
        ));
    }

    /**
     * @param array         $config
     * @param string        $locale
     * @param string        $fallback
     * @param null|Event    $event
     *
     * @throws \Exception
     *
     * @return Block
     */
    private function buildBlock(array $config, $locale, $fallback, ?Event $event = null)
    {
        $block = new Block($config['type'], $config['config'], $locale, $fallback);

        foreach ($config['children'] as $column => $children) {
            $block->addColumn($column);
            foreach ($children as $key => $child) {
                $child['key'] = $key;
                $child        = $this->doCreate($child, $locale, $fallback, $event);
                $block->addChild($column, $key, $child);
            }
        }

        return $block;
    }

    /**
     * @param array       $config
     * @param string      $locale
     * @param string      $fallback
     * @param null|Event  $event
     *
     * @throws NomenclatureNotFoundException
     *
     * @return mixed
     */
    private function buildObject(array $config, $locale, $fallback, ?Event $event = null)
    {
        $class  = $this->objects[$config['type']];
        $object = new $class($config['key'], $config['type'], $config['config'], $locale, $fallback);

        if ($object instanceof TemplateObject\Nomenclature) {
            if ('' === $object->getNomenclatureId()) {
                throw new NomenclatureNotFoundException();
            }

            if ($object->getNomenclatureId()) {
                $object->setNomenclature($this->getNomenclature(intval($object->getNomenclatureId())));
            }
        }

        if ($object instanceof ContextEventInterface && $event instanceof Event) {
            $object->setEvent($event);
        }

        return $object;
    }

    /**
     * @param int $id
     *
     * @throws NomenclatureNotFoundException
     *
     * @return Nomenclature
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
