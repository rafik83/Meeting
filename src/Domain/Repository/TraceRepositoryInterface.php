<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Trace;
use Proximum\Vimeet\Domain\Model\TraceableInterface;

interface TraceRepositoryInterface
{
    /**
     * @param Trace $trace
     */
    public function add(Trace $trace);

    /**
     * @param TraceableInterface $sheet
     *
     * @return Trace
     */
    public function getLastAcceptBySheet(TraceableInterface $sheet);

    /**
     * @param TraceableInterface $traceable
     *
     * @return Trace[]
     */
    public function getAllTracesByObject(TraceableInterface $traceable);
}
