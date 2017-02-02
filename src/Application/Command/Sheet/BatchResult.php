<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

class BatchResult
{
    /**
     * @var int
     */
    public $count;

    /**
     * Translation message key
     *
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $ignoredSheetsMessage;

    /**
     * BatchResult constructor.
     *
     * @param int    $count
     * @param string $message
     * @param string $ignoredSheetsMessage
     */
    public function __construct($count, $message, $ignoredSheetsMessage = '')
    {
        $this->count                = $count;
        $this->message              = $message;
        $this->ignoredSheetsMessage = $ignoredSheetsMessage;
    }
}
