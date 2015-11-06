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
class Type
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $position;

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
    private $participantTemplate;

    /**
     * @var string
     */
    private $sheetTemplate;

    /**
     * @var string
     */
    private $packageTemplate;

    /**
     * @var string
     */
    private $previewTemplate;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
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
     * Get preview
     *
     * @return mixed
     */
    public function getPreviewTemplate()
    {
        return $this->previewTemplate;
    }
}
