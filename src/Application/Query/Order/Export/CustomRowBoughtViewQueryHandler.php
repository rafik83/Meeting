<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\View\Order\Export\CustomRowBoughtView;

class CustomRowBoughtViewQueryHandler
{
    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param CustomRowBoughtViewQuery $query
     *
     * @return CustomRowBoughtView
     */
    public function handle(CustomRowBoughtViewQuery $query)
    {
        return new CustomRowBoughtView(
            $query->row->getId(),
            $query->row->getLabel(),
            $query->row->getPrice(),
            $query->row->getQuantity(),
            ($query->row->getQuantity() * $query->row->getPrice()) // total
        );
    }
}
