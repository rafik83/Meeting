<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class CancelRequest
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var string
     */
    public $message;

    /**
     * @var User
     */
    public $emitter;

    /**
     * @var \DateTimeInterface
     */
    public $date;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * CancelRequest constructor.
     *
     * @param Request            $request
     * @param User               $emitter
     * @param \DateTimeInterface $date
     * @param Sheet              $sheet
     */
    public function __construct(Request $request, User $emitter, \DateTimeInterface $date, Sheet $sheet)
    {
        $this->request = $request;
        $this->emitter = $emitter;
        $this->date    = $date;
        $this->sheet   = $sheet;
    }
}
