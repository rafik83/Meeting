<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class ParticipantImport
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var array
     */
    private $result;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * ParticipantImport constructor.
     *
     * @param Type               $type
     * @param array              $result
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Type $type, array $result, \DateTimeInterface $createdAt)
    {
        $this->type      = $type;
        $this->result    = $result;
        $this->createdAt = $createdAt;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
