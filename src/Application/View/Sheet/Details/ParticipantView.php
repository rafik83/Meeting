<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Details;

use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AgendaConfirmationStatusView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\PhoneValidationStatusView;
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

    /** @var AgendaConfirmationStatusView */
    public $agendaConfirmationStatus;

    /** @var PhoneValidationStatusView */
    public $phoneValidationStatusView;

    /**
     * @param int                          $id
     * @param string                       $email
     * @param TemplateData                 $templateData
     * @param bool                         $isOwner
     * @param bool                         $visio
     * @param AgendaConfirmationStatusView $agendaConfirmationStatus
     * @param PhoneValidationStatusView    $phoneValidationStatusView
     */
    public function __construct(
        $id,
        $email,
        TemplateData $templateData,
        $isOwner = false,
        bool $visio,
        AgendaConfirmationStatusView $agendaConfirmationStatus,
        PhoneValidationStatusView $phoneValidationStatusView
    ) {
        $this->id                        = $id;
        $this->templateData              = $templateData;
        $this->isOwner                   = $isOwner;
        $this->visio                     = $visio;
        $this->email                     = $email;
        $this->agendaConfirmationStatus  = $agendaConfirmationStatus;
        $this->phoneValidationStatusView = $phoneValidationStatusView;
    }

    /**
     * @return string
     */
    public function isVisio()
    {
        return $this->visio === true ? self::VISIO_CHECKED : '';
    }
}
