<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateData;

class UpdateProfile
{
    /**
     * @var TemplateData
     */
    public $templateData;

    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var array
     */
    public $data;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var User
     */
    public $user;

    /**
     * @param TemplateData $templateData
     * @param Participant  $participant
     * @param string       $locale
     * @param array        $data
     * @param User         $user
     */
    public function __construct(TemplateData $templateData, Participant $participant, $locale, $data, User $user)
    {
        $this->templateData = $templateData;
        $this->participant  = $participant;
        $this->locale       = $locale;
        $this->data         = $data;
        $this->user         = $user;
    }
}
