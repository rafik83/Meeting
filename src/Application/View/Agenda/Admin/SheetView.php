<?php

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
     * The sheet has a participant which is not available at all during the event
     * but has a meeting request explicitly associated to him/her
     *
     * @var bool
     */
    public $hasParticipantUnavailableWithMeetingRequest;

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
     * @var null|FollowerView
     */
    public $follower;

    /**
     * "La fiche a des créneaux disponibles"
     *
     * @var bool
     */
    public $hasAvailableSlots;

    /**
     * "A encore des demandes/propositions validées et non placées"
     *
     * @var bool
     */
    public $hasValidatedRequestNotScheduled;

    /**
     * Sheet is attending the event
     *
     * @var bool
     */
    public $attend;

    /**
     * @param int                 $id
     * @param string              $title
     * @param string              $type
     * @param int                 $countParticipant
     * @param bool                $attend
     * @param SheetIndicatorsView $sheetIndicatorsView
     * @param bool                $hasFollower
     * @param null|FollowerView   $follower
     * @param string              $url
     * @param array               $participants
     * @param bool                $hasParticipantUnavailableWithMeetingRequest
     */
    public function __construct(
        $id,
        $title,
        $type,
        $countParticipant,
        $attend,
        SheetIndicatorsView $sheetIndicatorsView,
        $hasFollower,
        $follower,
        $url,
        array $participants,
        $hasParticipantUnavailableWithMeetingRequest = false
    ) {
        $this->id                       = $id;
        $this->title                    = $title;
        $this->type                     = $type;
        $this->countParticipant         = $countParticipant;
        $this->attend                   = $attend;
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

        $this->hasParticipantUnavailableWithMeetingRequest = $hasParticipantUnavailableWithMeetingRequest;

        $this->hasNotEnoughAvailableSlot = $sheetIndicatorsView->hasNotEnoughAvailableSlot;
        $this->hasNotSentMeetingRequest  = $sheetIndicatorsView->hasNotSentMeetingRequest;
        $this->hasMeetingToApprove       = $sheetIndicatorsView->hasMeetingToApprove;

        $this->hasNotEnoughAvailableSlot       = $sheetIndicatorsView->hasNotEnoughAvailableSlot;
        $this->hasNotSentMeetingRequest        = $sheetIndicatorsView->hasNotSentMeetingRequest;
        $this->hasMeetingToApprove             = $sheetIndicatorsView->hasMeetingToApprove;
        $this->hasAvailableSlots               = $sheetIndicatorsView->hasAvailableSlots;
        $this->hasValidatedRequestNotScheduled = $sheetIndicatorsView->hasValidatedRequestNotScheduled;
    }
}
