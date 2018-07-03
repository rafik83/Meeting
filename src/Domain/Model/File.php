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
    public const TYPE_UNKNOWN = 'unknown';
    public const TYPE_UPLOADED_OBJECTS_ZIP = 'uploaded_objects_zip';

    /** @var int */
    private $id;

    /** @var string */
    private $path;

    /** @var string */
    private $type;

    /** @var \DateTimeInterface */
    private $createdAt;

    public function __construct($path, \DateTimeInterface $createdAt, string $type = self::TYPE_UNKNOWN)
    {
        $this->path = $path;
        $this->createdAt = $createdAt;
        $this->type = $type;
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

    public function getType(): ?string
    {
        return $this->type;
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
