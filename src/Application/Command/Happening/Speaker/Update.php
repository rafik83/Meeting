<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening\Speaker;

use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Update
{
    /**
     * @var Speaker
     */
    public $speaker;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $function;

    /**
     * @var string
     */
    public $organization;

    /**
     * @var UploadedFile
     */
    public $logo;

    /**
     * @var UploadedFile
     */
    public $photo;

    /**
     * Create constructor.
     *
     * @param Speaker $speaker
     */
    public function __construct(Speaker $speaker)
    {
        $this->speaker      = $speaker;
        $this->name         = $speaker->getName();
        $this->function     = $speaker->getFunction();
        $this->organization = $speaker->getOrganization();
    }
}
