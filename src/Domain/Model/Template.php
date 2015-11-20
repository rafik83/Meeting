<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

/**
 * "Template"
 */
class Template
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $participant;

    /**
     * @var array
     */
    private $sheet;

    /**
     * @var array
     */
    private $package;

    /**
     * @var string
     */
    private $preview;

    /**
     * @var string
     */
    private $view;

    /**
     * @var string
     */
    private $proForma;

    /**
     * Template constructor.
     *
     * @param string $title
     * @param array  $participant
     * @param array  $sheet
     * @param array  $package
     * @param string $preview
     * @param string $view
     */
    public function __construct($title, array $participant, array $sheet, array $package, $preview, $view)
    {
        $this->title       = $title;
        $this->participant = $participant;
        $this->sheet       = $sheet;
        $this->package     = $package;
        $this->preview     = $preview;
        $this->view        = $view;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get participant
     *
     * @return array
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * Get sheet
     *
     * @return array
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * Get package
     *
     * @return array
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Get preview
     *
     * @return string
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * Get view
     *
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return string
     */
    public function getProForma()
    {
        return $this->proForma;
    }
}
