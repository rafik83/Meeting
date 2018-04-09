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
    /** @var array */
    public $sheets;

    /** @var int */
    public $count;

    /**
     * Translation message key
     *
     * @var string
     */
    public $message;

    /**
     * Sheets title joined by a comma
     *
     * @var string
     */
    public $ignoredSheetsMessage;

    /**
     * @param array  $sheets
     * @param string $message
     * @param string $ignoredSheetsMessage
     */
    public function __construct(array $sheets, $message, $ignoredSheetsMessage = '')
    {
        $this->sheets = $sheets;
        $this->count = \count($sheets);
        $this->message = $message;
        $this->ignoredSheetsMessage = $ignoredSheetsMessage;
    }
}
