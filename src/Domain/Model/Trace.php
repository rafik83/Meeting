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
    const ACCEPT   = 'accept';
    const VALIDATE = 'validate';
    const CREATE   = 'create';
    const UPDATE   = 'update';

    /**
     * @var int
     */
    private $id;

    /**
     * Composed of TraceableName + ID
     * @var string
     */
    private $object;

    /**
     * @var string
     */
    private $action;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Admin
     */
    private $admin;

    /**
     * @var DateTimeInterface
     */
    private $date;

    /**
     * @var string
     */
    private $comment;

    /**
     * @param TraceableInterface $traceable
     * @param string             $action
     * @param DateTimeInterface  $date
     * @param string             $comment
     * @param AbstractUser|null  $abstractUser
     */
    public function __construct (
        TraceableInterface $traceable,
        $action,
        DateTimeInterface $date,
        $comment,
        AbstractUser $abstractUser = null
    ) {
        $this->object  = sprintf('%s%s', $traceable->getTraceableName(), $traceable->getId());
        $this->action  = $action;
        $this->date    = $date;
        $this->comment = $comment;

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
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param Trace[]            $traces
     * @param TraceableInterface $traceable
     *
     * @return Trace|null
     */
    public static function find(array &$traces, TraceableInterface $traceable)
    {
        foreach ($traces as $trace) {
            if ($trace->getObject() === self::identifier($traceable)) {
                return $trace;
            }
        }

        return null;
    }

    /**
     * @param TraceableInterface $traceable
     *
     * @return string
     */
    public static function identifier(TraceableInterface $traceable)
    {
        return $traceable->getTraceableName().$traceable->getId();
    }
}
