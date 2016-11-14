<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Filter;

use Symfony\Component\HttpFoundation\Session\Session;

class SheetFilter
{
    const SHEET_FILTER = 'sheet_filters';

    /**
     * @var Session
     */
    private $session;

    /**
     * SheetFilter constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @return array|null
     */
    public function get()
    {
        return $this->session->get(self::SHEET_FILTER);
    }

    /**
     * @param array $filters
     */
    public function add(array $filters)
    {
        if (isset($filters['page'])) {
            unset($filters['page']);
        }

        $this->session->set(self::SHEET_FILTER,
            array_filter($filters, function ($filter) {
                return $filter !== null;
            })
        );
    }

    /**
     *  Clear sheet filters
     */
    public function clear()
    {
        $this->session->remove(self::SHEET_FILTER);
    }
}
