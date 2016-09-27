<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet;

use Proximum\Vimeet\Domain\Model\Trace;

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
     * @var string
     */
    public $traceAction;

    /**
     * @var \DateTimeInterface
     */
    public $traceAt = null;

    /**
     * @var string
     */
    public $traceBy = null;

    /**
     * @var int
     */
    public $completeness;

    /**
     * @var bool
     */
    public $enabled;

    /**
     * @var bool
     */
    public $inCatalog;

    /**
     * SheetListView constructor.
     *
     * @param int                  $id
     * @param string               $title
     * @param string               $state
     * @param int                  $completeness
     * @param bool                 $enabled
     * @param bool                 $inCatalog
     * @param array                $categories
     * @param string               $type
     * @param SheetParticipantView $owner
     * @param string               $follower
     * @param \DateTimeInterface   $createdAt
     * @param \DateTimeInterface   $lastLoginAt
     * @param string               $impersonationToken
     * @param Trace|null           $trace
     */
    public function __construct(
        $id,
        $title,
        $state,
        $completeness,
        $enabled,
        $inCatalog,
        array $categories,
        $type,
        SheetParticipantView $owner,
        $follower,
        \DateTimeInterface $createdAt,
        \DateTimeInterface $lastLoginAt,
        $impersonationToken,
        Trace $trace = null
    ) {
        $this->id                 = $id;
        $this->title              = $title;
        $this->state              = $state;
        $this->completeness       = $completeness;
        $this->enabled            = $enabled;
        $this->inCatalog          = $inCatalog;
        $this->categories         = $categories;
        $this->type               = $type;
        $this->owner              = $owner;
        $this->follower           = $follower;
        $this->createdAt          = $createdAt;
        $this->lastLoginAt        = $lastLoginAt;
        $this->impersonationToken = $impersonationToken;

        if (null !== $trace) {
            $this->traceAction = $trace->getAction();
            $this->traceAt     = $trace->getDate();
            $this->traceBy     = $trace->getAuthor();
        }
    }

    /**
     * @return bool
     */
    public function isIncomplete()
    {
        return 100 !== $this->completeness;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true === $this->enabled;
    }

    /**
     * @return bool
     */
    public function isInCatalog()
    {
        return $this->inCatalog;
    }

    /**
     * @return string
     */
    public function completenessStatus()
    {
        if ($this->completeness < 40) {
            return 'danger';
        }

        if ($this->completeness < 100) {
            return 'warning';
        }

        if ($this->completeness === 100) {
            return 'success';
        }

        return 'danger';
    }
}
