<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class File
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $path;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @param string             $path
     * @param \DateTimeInterface $createdAt
     */
    public function __construct($path, \DateTimeInterface $createdAt)
    {
        $this->path      = $path;
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
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return hash('sha256', $this->getPath() . $this->getCreatedAt()->format('YmdHis') . $this->getId());
    }
}
