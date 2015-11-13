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

/**
 * "Type de participation"
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
     * @var string
     */
    private $participantTemplate = [];

    /**
     * @var string
     */
    private $sheetTemplate = [];

    /**
     * @var string
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
     * Type constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event        = $event;
        $this->translations = new ArrayCollection();
        $this->categories   = new ArrayCollection();
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
     * Get participantTemplate
     *
     * @return array
     */
    public function getParticipantTemplate()
    {
        return $this->participantTemplate;
    }

    /**
     * Get sheetTemplate
     *
     * @return array
     */
    public function getSheetTemplate()
    {
        return $this->sheetTemplate;
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
     * Get packageTemplate
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
     * @param string $participantTemplate
     */
    public function setParticipantTemplate($participantTemplate)
    {
        $this->participantTemplate = $participantTemplate;
    }

    /**
     * @param string $sheetTemplate
     */
    public function setSheetTemplate($sheetTemplate)
    {
        $this->sheetTemplate = $sheetTemplate;
    }

    /**
     * @param string $packageTemplate
     */
    public function setPackageTemplate($packageTemplate)
    {
        $this->packageTemplate = $packageTemplate;
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
     * Get preview
     *
     * @return mixed
     */
    public function getPreviewTemplate()
    {
        return $this->previewTemplate;
    }

    /**
     * Get viewTemplate
     *
     * @return string
     */
    public function getViewTemplate()
    {
        return $this->viewTemplate;
    }

    /**
     * Get categories
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
        $this->sheetTemplate       = $template->getSheet();
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
}
