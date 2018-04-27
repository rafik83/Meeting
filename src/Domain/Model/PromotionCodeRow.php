<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class PromotionCodeRow
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
     * @var PromotionCode
     */
    private $promotionCode;

    /**
     * @param Sheet         $sheet
     * @param PromotionCode $promotionCode
     */
    public function __construct(Sheet $sheet, PromotionCode $promotionCode)
    {
        $this->sheet         = $sheet;
        $this->promotionCode = $promotionCode;
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
     * @return PromotionCode
     */
    public function getPromotionCode()
    {
        return $this->promotionCode;
    }

    /**
     * @param PromotionCode $promotionCode
     */
    public function setPromotionCode($promotionCode)
    {
        $this->promotionCode = $promotionCode;
    }
}
