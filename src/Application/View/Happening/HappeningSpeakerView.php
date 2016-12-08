<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Happening;

class HappeningSpeakerView
{
    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $position;

    /**
     * @var string
     */
    private $picture;

    /**
     * HappeningSpeakerView constructor.
     *
     * @param string $firstname
     * @param string $lastname
     * @param string $position
     * @param string $picture
     */
    public function __construct($firstname, $lastname, $position, $picture)
    {
        $this->firstname = $firstname;
        $this->lastname  = $lastname;
        $this->position  = $position;
        $this->picture   = $picture;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @return bool
     */
    public function hasPicture()
    {
        return !empty($this->picture);
    }
}
