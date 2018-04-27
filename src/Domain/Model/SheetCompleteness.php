<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class SheetCompleteness
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var int
     */
    private $completeness;

    /**
     * SheetCompleteness constructor.
     *
     * @param Sheet  $sheet
     * @param string $locale
     * @param int    $completeness
     */
    public function __construct(Sheet $sheet, $locale, $completeness)
    {
        $this->sheet        = $sheet;
        $this->locale       = $locale;
        $this->completeness = $completeness;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return int
     */
    public function getCompleteness()
    {
        return $this->completeness;
    }

    /**
     * @param int $completeness
     */
    public function setCompleteness($completeness)
    {
        $this->completeness = $completeness;
    }
}
