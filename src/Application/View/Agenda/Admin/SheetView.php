<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Admin;

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
     * "Nom du suivi commercial"
     *
     * @var string|null
     */
    public $follower;

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
     * @param int         $id
     * @param string      $title
     * @param string      $type
     * @param int         $countParticipant
     * @param int         $countRequest
     * @param int         $countProposition
     * @param int         $countValidatedRequest
     * @param int         $countSlots
     * @param int         $usableSlots
     * @param int         $countPlacedMeetings
     * @param int         $countPendingPropositions
     * @param string|null $follower
     * @param string      $url
     */
    public function __construct(
        $id,
        $title,
        $type,
        $countParticipant,
        $countRequest,
        $countProposition,
        $countValidatedRequest,
        $countSlots,
        $usableSlots,
        $countPlacedMeetings,
        $countPendingPropositions,
        $follower,
        $url
    ) {
        $this->id                       = $id;
        $this->title                    = $title;
        $this->type                     = $type;
        $this->countParticipant         = $countParticipant;
        $this->countRequest             = $countRequest;
        $this->countProposition         = $countProposition;
        $this->countValidatedRequest    = $countValidatedRequest;
        $this->countSlots               = $countSlots;
        $this->countPlacedMeetings      = $countPlacedMeetings;
        $this->usableSlots              = $usableSlots;
        $this->countPendingPropositions = $countPendingPropositions;
        $this->url                      = $url;
        $this->follower                 = $follower;

        $this->hasNotSentMeetingRequest  = $this->hasNotSentMeetingRequest();
        $this->hasNotEnoughAvailableSlot = $this->hasNotEnoughAvailableSlot();
        $this->hasMeetingToApprove       = $this->hasMeetingToApprove();
    }

    /**
     * @return bool
     */
    private function hasNotSentMeetingRequest()
    {
        return $this->countRequest === 0;
    }

    /**
     * @return bool
     */
    private function hasMeetingToApprove()
    {
        return $this->countPendingPropositions > 0;
    }

    /**
     * @return bool
     */
    private function hasNotEnoughAvailableSlot()
    {
        if ($this->usableSlots === 0) {
            return true;
        }

        return (($this->countValidatedRequest - $this->countPlacedMeetings) / $this->usableSlots) > 1;
    }
}
