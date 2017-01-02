<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Happening\Admin;

use Proximum\Vimeet\Domain\Model\Happening;

class HappeningParticipantView
{
    /**
     * id conf, nom de la conf, jour conf,
     * heure conf, id fiche, id participant, champ question (s'il y a des questions sur ce ss événement),
     * email, nom, prénom, fonction, nom fiche.
     */

    /**
     * @var Happening
     */
    private $happening;

    /**
     * @var int
     */
    private $sheetId;

    /**
     * @var int
     */
    private $participantId;

    /**
     * @var string
     */
    private $question;

    /**
     * @var string
     */
    private $email;

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
    private $fonction;

    /**
     * @var string
     */
    private $sheetName;

    /**
     * HappeningParticipantView constructor.
     *
     * @param Happening $happening
     * @param int       $sheetId
     * @param int       $participantId
     * @param string    $question
     * @param string    $email
     * @param string    $firstname
     * @param string    $lastname
     * @param string    $fonction
     * @param string    $sheetName
     */
    public function __construct(
        Happening $happening,
        $sheetId,
        $participantId,
        $question,
        $email,
        $firstname,
        $lastname,
        $fonction,
        $sheetName
    ) {
        $this->happening     = $happening;
        $this->sheetId       = $sheetId;
        $this->participantId = $participantId;
        $this->question      = $question;
        $this->email         = $email;
        $this->firstname     = $firstname;
        $this->lastname      = $lastname;
        $this->fonction      = $fonction;
        $this->sheetName     = $sheetName;
    }

    /**
     * @return Happening
     */
    public function getHappening()
    {
        return $this->happening;
    }

    /**
     * @return int
     */
    public function getSheetId()
    {
        return $this->sheetId;
    }

    /**
     * @return int
     */
    public function getParticipantId()
    {
        return $this->participantId;
    }

    /**
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
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
    public function getFonction()
    {
        return $this->fonction;
    }

    /**
     * @return string
     */
    public function getSheetName()
    {
        return $this->sheetName;
    }
}
