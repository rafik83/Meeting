<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Details;

use Proximum\Vimeet\Domain\Template\TemplateData;

class ParticipantView
{
    const VISIO_CHECKED = 'checked';

    /** @var int */
    public $id;

    /** @var TemplateData */
    public $templateData;

    /** @var bool */
    public $isOwner;

    /** @var bool */
    public $visio;

    /** @var string */
    public $email;

    /** @var array */
    public $agendaConfirmationStatus;

    /**
     * @param int          $id
     * @param string       $email
     * @param TemplateData $templateData
     * @param bool         $isOwner
     * @param bool         $visio
     * @param array        $agendaConfirmationStatus
     */
    public function __construct(
        $id,
        $email,
        TemplateData $templateData,
        $isOwner = false,
        bool $visio,
        array $agendaConfirmationStatus
    ) {
        $this->id                = $id;
        $this->templateData      = $templateData;
        $this->isOwner           = $isOwner;
        $this->visio             = $visio;
        $this->email             = $email;
        $this->agendaConfirmationStatus = $agendaConfirmationStatus;
    }

    /**
     * @return string
     */
    public function isVisio()
    {
        return $this->visio === true ? self::VISIO_CHECKED : '';
    }
}
