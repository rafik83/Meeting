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
use Proximum\Vimeet\Domain\Model\Sheet\Template as SheetTemplate;

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
     * @var array
     */
    private $participantTemplate = [];

    /**
     * @var array
     */
    private $oldSheetTemplate = [];

    /**
     * @var SheetTemplate
     */
    private $sheetTemplate;

    /**
     * @var array
     */
    private $packageTemplate = [];

    /**
     * @var int
     */
    private $maxParticipant = 4;

    /**
     * @var int
     */
    private $freeParticipant = 1;

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
     * Get participantTemplate.
     *
     * @return array
     */
    public function getParticipantTemplate()
    {
        return $this->participantTemplate;
    }

    /**
     * Get sheetTemplate.
     *
     * @return array
     */
    public function getSheetTemplate()
    {
        return $this->getOldSheetTemplate();
    }

    /**
     * Get sheetTemplate.
     *
     * @return array
     */
    public function getOldSheetTemplate()
    {
        return $this->oldSheetTemplate;
    }

    /**
     * @return array
     */
    public function getSheetData()
    {
        $data = [];

        $template = $this->getSheetTemplate();

        foreach ($template as $key => $block) {
            $data[$key] = [];

            foreach ($block['template'] as $i => $row) {
                $data[$key][$i] = null;
            }
        }

        return $data;
    }

    /**
     * Get packageTemplate.
     *
     * @return array
     */
    public function getPackageTemplate()
    {
        return $this->packageTemplate;
    }

    /**
     * @return array
     */
    public function getPackageData()
    {
        $data = [];

        $template = $this->getPackageTemplate();

        foreach ($template as $key => $block) {
            $data[$key] = [];

            foreach ($block['template'] as $i => $row) {
                $data[$key][$i] = null;
            }
        }

        return $data;
    }

    /**
     * @param array $participantTemplate
     *
     * @return self
     */
    public function setParticipantTemplate(array $participantTemplate)
    {
        $this->participantTemplate = $participantTemplate;

        return $this;
    }

    /**
     * @param SheetTemplate $sheetTemplate
     *
     * @return self
     */
    public function setSheetTemplate(SheetTemplate $sheetTemplate)
    {
        $this->sheetTemplate = $sheetTemplate;

        return $this;
    }

    /**
     * @param array $sheetTemplate
     *
     * @return self
     */
    public function setOldSheetTemplate(array $sheetTemplate)
    {
        $this->oldSheetTemplate = $sheetTemplate;

        return $this;
    }


    /**
     * @param array $packageTemplate
     *
     * @return Type
     */
    public function setPackageTemplate(array $packageTemplate)
    {
        $this->packageTemplate = $packageTemplate;

        return $this;
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
    public function getFreeParticipant()
    {
        return $this->freeParticipant;
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
     * @param Template $template
     */
    public function setTemplate(Template $template)
    {
        $this->participantTemplate = $template->getParticipant();
        $this->oldSheetTemplate    = $template->getSheet();
        $this->packageTemplate     = $template->getPackage();
        $this->previewTemplate     = $template->getPreview();
        $this->viewTemplate        = $template->getView();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'type';
    }

    /**
     * @param $templateName
     *
     * @return array
     * @throws \Exception
     */
    public function getTemplate($templateName)
    {
        $getter = 'get' . ucfirst($templateName) . 'Template';

        if (!method_exists($this, $getter)) {
            throw new \Exception("Method $getter not exists");
        }

        return $this->$getter();
    }

    /**
     * @return ValidationCriteria
     */
    public function getValidationCriteria()
    {
        return $this->validationCriteria;
    }

    /**
     * @param string $name
     * @param array $template
     *
     * @return self
     * @throws \Exception
     */
    public function setTemplateByName($name, array $template)
    {
        if (ucfirst($name) === 'Sheet') {
            $setter = 'setOldSheetTemplate';
        } else {
            $setter = 'set' . ucfirst($name) . 'Template';
        }

        if (!method_exists($this, $setter)) {
            throw new \Exception("Method $setter not exists");
        }

        return $this->$setter($template);
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return [
            'participantTemplate' => $this->getParticipantTemplate(),
            'sheetTemplate'       => $this->getSheetTemplate(),
            'packageTemplate'     => $this->getPackageTemplate(),
        ];
    }

    /**
     * @param string $templateName
     * @param string $group
     * @param string $row
     * @param array  $options
     *
     * @return self
     * @throws \Exception
     */
    public function updateTemplateRow($templateName, $group, $row, array $options)
    {
        $template = $this->getTemplate($templateName);

        if ('default' === $group) {
            if (!isset($template[$row])) {
                throw new \Exception("$row do not exists in template $templateName");
            }

            $template[$row] = $options;
        } else {
            if (!isset($template[$group])) {
                throw new \Exception("$group do not exists in template $templateName");
            }

            if (!isset($template[$group]['template'][$row])) {
                throw new \Exception("$group / $row do not exists in template $templateName");
            }

            $template[$group]['template'][$row] = $options;
        }

        return $this->setTemplateByName($templateName, $template);
    }

    /**
     * @param string $templateName
     * @param string $group
     * @param string $row
     * @param array  $options
     *
     * @return self
     * @throws \Exception
     */
    public function addTemplateRow($templateName, $group, $row, array $options)
    {
        $template = $this->getTemplate($templateName);

        if ('default' === $group) {
            if (isset($template[$row])) {
                throw new \Exception("$row already exists in template $templateName");
            }

            $template[$row] = $options;
        } else {
            if (!isset($template[$group])) {
                throw new \Exception("$group do not exists in template $templateName");
            }

            if (isset($template[$group]['template'][$row])) {
                throw new \Exception("$group / $row already exists in template $templateName");
            }

            $template[$group]['template'][$row] = $options;
        }

        return $this->setTemplateByName($templateName, $template);
    }

    /**
     * @param string $templateName
     * @param string $group
     * @param array  $rows
     *
     * @return self
     * @throws \Exception
     */
    public function setTemplateRows($templateName, $group, array $rows)
    {
        $template = $this->getTemplate($templateName);

        if ('default' === $group) {
            $template = [];

            foreach ($rows as $row => $options) {
                $template[$row] = $options;
            }
        } else {
            if (!isset($template[$group])) {
                throw new \Exception("$group do not exists in template $templateName");
            }

            $template[$group]['template'] = [];

            foreach ($rows as $row => $options) {
                $template[$group]['template'][$row] = $options;
            }
        }

        return $this->setTemplateByName($templateName, $template);
    }
}
