<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Admin;

use Proximum\Vimeet\Application\View\Agenda\Admin\Indicator\SheetIndicatorsView;

class SheetView
{
    /**
     * Sheet id
     *
     * @var int
     */
    public $id;

    /**
     * Sheet title
     *
     * @var string
     */
    public $title;

    /**
     * Sheet participation type title
     *
     * @var string
     */
    public $type;

    /**
     * "Nombre de participants de la fiche"
     *
     * @var int
     */
    public $countParticipant;

    /**
     * "Nombre de demandes de RDV"
     *
     * @var int
     */
    public $countRequest;

    /**
     * "Nombre de propositions de RDV"
     *
     * @var int
     */
    public $countProposition;

    /**
     * "Nombre de demandes et propositions de RDV acceptées"
     *
     * @var int
     */
    public $countValidatedRequest;

    /**
     * "Nombre de slots en tenant compte du nombre de planning"
     *
     * @var int
     */
    public $countSlots;

    /**
     * "Nombre de RDV placés"
     *
     * @var int
     */
    public $countPlacedMeetings;

    /**
     * "Slots utilisables"
     *
     * @var int
     */
    public $usableSlots;

    /**
     * "Propositions en attente de validation"
     *
     * @var int
     */
    public $countPendingPropositions;

    /**
     * Sheet details url
     *
     * @var string
     */
    public $url;

    /**
     * "La fiche a-t-elle effectuée aucune demandes de rendez-vous"
     *
     * @var bool
     */
    public $hasNotSentMeetingRequest;

    /**
     * "La fiche a-t-elle des demandes de rendez-vous en attente de validation"
     *
     * @var bool
     */
    public $hasMeetingToApprove;

    /**
     * "La fiche a-t-elle assez de creneaux disponible"
     *
     * @var bool
     */
    public $hasNotEnoughAvailableSlot;

    /**
     * @var bool
     */
    public $hasFollower;

    /**
     * @var array
     */
    public $participants;

    /**
     * Suivi commercial
     *
     * @var FollowerView
     */
    public $follower;

    /**
     * @param int                 $id
     * @param string              $title
     * @param string              $type
     * @param int                 $countParticipant
     * @param SheetIndicatorsView $sheetIndicatorsView
     * @param bool                $hasFollower
     * @param null|FollowerView        $follower
     * @param string              $url
     * @param array               $participants
     */
    public function __construct(
        $id,
        $title,
        $type,
        $countParticipant,
        SheetIndicatorsView $sheetIndicatorsView,
        $hasFollower,
        $follower,
        $url,
        array $participants
    ) {
        $this->id                       = $id;
        $this->title                    = $title;
        $this->type                     = $type;
        $this->countParticipant         = $countParticipant;
        $this->countRequest             = $sheetIndicatorsView->countRequest;
        $this->countProposition         = $sheetIndicatorsView->countProposition;
        $this->countValidatedRequest    = $sheetIndicatorsView->countValidatedRequest;
        $this->countSlots               = $sheetIndicatorsView->countSlots;
        $this->countPlacedMeetings      = $sheetIndicatorsView->countPlacedMeetings;
        $this->usableSlots              = $sheetIndicatorsView->usableSlots;
        $this->countPendingPropositions = $sheetIndicatorsView->countPendingPropositions;
        $this->hasFollower              = $hasFollower;
        $this->url                      = $url;
        $this->participants             = $participants;
        $this->follower                 = $follower;

        $this->hasNotEnoughAvailableSlot = $sheetIndicatorsView->hasNotEnoughAvailableSlot;
        $this->hasNotSentMeetingRequest  = $sheetIndicatorsView->hasNotSentMeetingRequest;
        $this->hasMeetingToApprove       = $sheetIndicatorsView->hasMeetingToApprove;
    }
}
