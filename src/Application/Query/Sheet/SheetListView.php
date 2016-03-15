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
     * @param array                $categories
     * @param string               $type
     * @param SheetParticipantView $owner
     * @param \DateTimeInterface   $createdAt
     * @param \DateTimeInterface   $lastLoginAt
     * @param string               $impersonationToken
     */
    public function __construct(
        $id,
        $title,
        array $categories,
        $type,
        SheetParticipantView $owner,
        \DateTimeInterface $createdAt,
        \DateTimeInterface $lastLoginAt,
        $impersonationToken
    ) {
        $this->id                 = $id;
        $this->title              = $title;
        $this->categories         = $categories;
        $this->type               = $type;
        $this->owner              = $owner;
        $this->createdAt          = $createdAt;
        $this->lastLoginAt        = $lastLoginAt;
        $this->impersonationToken = $impersonationToken;
    }
}
