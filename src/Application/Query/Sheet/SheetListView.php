<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

class SheetListView
{
    /**
     * @var int
     */
    public $id;

    /**
     * "Titre fiche / Société"
     *
     * @var string
     */
    public $title;

    /**
     * "Etat de la fiche"
     *
     * @var string
     */
    public $state;

    /**
     * "Catégorie"
     *
     * @var array
     */
    public $categories;

    /**
     * "Type de participation"
     *
     * @var string
     */
    public $type;

    /**
     * "Nom, prénom, email du propriétaire de la fiche"
     *
     * @var SheetParticipantView
     */
    public $owner;

    /**
     * "Suivi commercial"
     *
     * @var string
     */
    public $follower;

    /**
     * "Date de création de la fiche"
     *
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * "Date de dernière connexion d'un des participants"
     *
     * @var \DateTimeInterface
     */
    public $lastLoginAt;

    /**
     * @var string
     */
    public $impersonationToken;

    /**
     * SheetListView constructor.
     *
     * @param int                  $id
     * @param string               $title
     * @param string               $state
     * @param bool                 $completed
     * @param array                $categories
     * @param string               $type
     * @param SheetParticipantView $owner
     * @param string               $follower
     * @param \DateTimeInterface   $createdAt
     * @param \DateTimeInterface   $lastLoginAt
     * @param string               $impersonationToken
     */
    public function __construct(
        $id,
        $title,
        $state,
        $completed,
        array $categories,
        $type,
        SheetParticipantView $owner,
        $follower,
        \DateTimeInterface $createdAt,
        \DateTimeInterface $lastLoginAt,
        $impersonationToken
    ) {
        $this->id                 = $id;
        $this->title              = $title;
        $this->state              = $state;
        $this->completed          = $completed;
        $this->categories         = $categories;
        $this->type               = $type;
        $this->owner              = $owner;
        $this->follower           = $follower;
        $this->createdAt          = $createdAt;
        $this->lastLoginAt        = $lastLoginAt;
        $this->impersonationToken = $impersonationToken;
    }

    /**
     * @return bool
     */
    public function isIncomplete()
    {
        return false === $this->completed;
    }
}
