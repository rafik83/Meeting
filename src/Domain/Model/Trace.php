<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;

/**
 * Traçabilité
 */
class Trace
{
    const ACCEPT                         = 'accept';
    const VALIDATE                       = 'validate';
    const CREATE                         = 'create';
    const UPDATE                         = 'update';
    const ENABLE                         = 'enable';
    const DISABLE                        = 'disable';
    const ENABLE_CATALOG                 = 'enable_catalog';
    const DISABLE_CATALOG                = 'disable_catalog';
    const CHANGED_TYPE                   = 'changed_type';
    const VALIDATION_DRAFT               = 'validation_draft';
    const VALIDATION_VALIDATE            = 'validation_validate';
    const PARTICIPANT_IMPORTED           = 'participant_imported';
    const SHEET_CREATED_BY_GROUP_MANAGER = 'sheet_created_by_group_manager';
    const PENDING                        = 'pending';
    const SET_COMMERCIAL_STATUS          = 'set_commercial_status';

    const ACTIONS_REQUIRED_TRANSLATION = [
        self::SET_COMMERCIAL_STATUS,
    ];

    /** @var int */
    private $id;

    /** @var string */
    private $action;

    /** @var User|null */
    private $user;

    /** @var Admin|null */
    private $admin;

    /** @var DateTimeInterface */
    private $date;

    /** @var string */
    private $comment;

    /** @var string */
    private $objectType;

    /** @var int */
    private $objectId;

    /**
     * @param TraceableInterface $traceable
     * @param string             $action
     * @param DateTimeInterface  $date
     * @param string             $comment
     * @param null|AbstractUser  $abstractUser
     */
    public function __construct(
        TraceableInterface $traceable,
        $action,
        DateTimeInterface $date,
        $comment,
        AbstractUser $abstractUser = null
    ) {
        $this->objectType = $traceable->getTraceableName();
        $this->objectId   = $traceable->getId();
        $this->action     = $action;
        $this->date       = $date;
        $this->comment    = $comment;

        if ($abstractUser instanceof User) {
            $this->user = $abstractUser;
        } elseif ($abstractUser instanceof Admin) {
            $this->admin = $abstractUser;
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        if (null !== $this->user) {
            return $this->user->getEmail();
        } elseif (null !== $this->admin) {
            return sprintf('%s %s', $this->admin->getFirstname(), $this->admin->getLastname());
        }

        return '';
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param Trace[]            $traces
     * @param TraceableInterface $traceable
     *
     * @return null|Trace
     */
    public static function find(array &$traces, TraceableInterface $traceable)
    {
        foreach ($traces as $trace) {
            if ($trace->getObjectType() === $traceable->getTraceableName()
                && $trace->getObjectId() === $traceable->getId()
            ) {
                return $trace;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasToBeTranslated(): bool
    {
        return in_array($this->action, self::ACTIONS_REQUIRED_TRANSLATION, true);
    }
}
