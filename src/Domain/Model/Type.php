<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;

/**
 * "Type de participation".
 */
class Type implements WhoInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var SheetTemplate
     */
    private $sheetTemplate;

    /**
     * @var RegistrationTemplate
     */
    private $registrationTemplate;

    /**
     * @var int
     */
    private $maxParticipant = 4;

    /**
     * @var int
     */
    private $maxPlanning = 4;

    /**
     * @var string
     */
    private $previewTemplate = '';

    /**
     * @var string
     */
    private $viewTemplate = '';

    /**
     * @var ArrayCollection
     */
    private $categories;

    /**
     * @var ValidationCriteria
     */
    private $validationCriteria;

    /**
     * Type constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event              = $event;
        $this->translations       = new ArrayCollection();
        $this->categories         = new ArrayCollection();
        $this->validationCriteria = new ValidationCriteria(false);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getTitle($locale)
    {
        return $this->getTranslations()->containsKey($locale) ? $this->getTranslations()->get($locale)->getTitle() : '';
    }

    /**
     * @return SheetTemplate
     */
    public function getSheetTemplate()
    {
        return $this->sheetTemplate;
    }

    /**
     * @return SheetTemplate
     *
     * @deprecated Use getSheetTemplate()
     */
    public function getNewSheetTemplate()
    {
        return $this->getSheetTemplate();
    }

    /**
     * @param SheetTemplate $sheetTemplate
     *
     * @return Type
     */
    public function setSheetTemplate(SheetTemplate $sheetTemplate)
    {
        $this->sheetTemplate = $sheetTemplate;

        return $this;
    }

    /**
     * @param RegistrationTemplate $registrationTemplate
     *
     * @return Type
     */
    public function setRegistrationTemplate(RegistrationTemplate $registrationTemplate)
    {
        $this->registrationTemplate = $registrationTemplate;

        return $this;
    }

    /**
     * @return RegistrationTemplate
     */
    public function getRegistrationTemplate()
    {
        return $this->registrationTemplate;
    }

    /**
     * @return int
     */
    public function getMaxParticipant()
    {
        return $this->maxParticipant;
    }

    /**
     * @return int
     */
    public function getMaxPlanning()
    {
        return $this->maxPlanning;
    }

    /**
     * Get preview.
     *
     * @return string
     */
    public function getPreviewTemplate()
    {
        return $this->previewTemplate;
    }

    /**
     * Get viewTemplate.
     *
     * @return string
     */
    public function getViewTemplate()
    {
        return $this->viewTemplate;
    }

    /**
     * Get categories.
     *
     * @return ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'type';
    }

    /**
     * @return ValidationCriteria
     */
    public function getValidationCriteria()
    {
        return $this->validationCriteria;
    }

    /**
     * @param int $position
     *
     * @return Type
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return array
     *
     * @deprecated Use getRegistrationTemplate()
     */
    public function getParticipantTemplate()
    {
        return [];
    }

    /**
     * @param string $locale
     *
     * @return array
     */
    public function getCategoriesTitles($locale)
    {
        return $this->categories->map(function (Category $category) use ($locale) {
            return $category->getTitle($locale);
        })->toArray();
    }
}
