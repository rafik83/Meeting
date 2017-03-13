<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Invoice;

class PromotionCodeView
{
    /** @var string */
    public $label;

    /** @var string */
    public $description;

    /** @var int in cents */
    public $total;

    /** @var int */
    public $quantity;

    /** @var PromotionProductRowView[] */
    public $promotionProductRowViews;

    /**
     * @param string                    $label
     * @param string                    $description
     * @param float                     $total
     * @param int                       $quantity
     * @param PromotionProductRowView[] $promotionProductRowViews
     */
    public function __construct($label, $description, $total, $quantity, $promotionProductRowViews = [])
    {
        $this->label                    = $label;
        $this->description              = $description;
        $this->total                    = $total;
        $this->quantity                 = $quantity;
        $this->promotionProductRowViews = $promotionProductRowViews;
    }
}
