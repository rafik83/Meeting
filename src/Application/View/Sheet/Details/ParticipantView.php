<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Details;

use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AgendaConfirmationStatusView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\AvailabilityConfirmationView;
use Proximum\Vimeet\Application\View\Sheet\Details\Participant\PhoneValidationStatusView;
use Proximum\Vimeet\Domain\Template\TemplateData;

class ParticipantView
{
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

    /** @var AvailabilityConfirmationView */
    public $availabilityConfirmationView;

    /** @var int */
    public $userId;

    public function __construct(
        int $id,
        string $email,
        TemplateData $templateData,
        bool $isOwner,
        bool $visio,
        AgendaConfirmationStatusView $agendaConfirmationStatus,
        PhoneValidationStatusView $phoneValidationStatusView,
        AvailabilityConfirmationView $availabilityConfirmationView,
        int $userId
    ) {
        $this->id = $id;
        $this->templateData = $templateData;
        $this->isOwner = $isOwner;
        $this->visio = $visio;
        $this->email = $email;
        $this->agendaConfirmationStatus = $agendaConfirmationStatus;
        $this->phoneValidationStatusView = $phoneValidationStatusView;
        $this->availabilityConfirmationView = $availabilityConfirmationView;
        $this->userId = $userId;
    }
}
