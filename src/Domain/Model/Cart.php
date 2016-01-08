<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;

class Cart
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $template;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * @param array             $data
     * @param array             $template
     * @param Sheet             $sheet
     * @param DateTimeInterface $createdAt
     */
    public function __construct(array $data, array $template, Sheet $sheet, DateTimeInterface $createdAt)
    {
        $this->data      = $data;
        $this->template  = $template;
        $this->sheet     = $sheet;
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
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
