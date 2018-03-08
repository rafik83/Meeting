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
     * @param TraceableInterface[] $objects
     * @param string               $type
     * @param string               $action
     *
     * @return Trace[]
     */
    public function getLastByTraceableObjectsAndAction(array $objects, $type, $action);

    /**
     * @param TraceableInterface $traceable
     *
     * @return Trace[]
     */
    public function getAllTracesByObject(TraceableInterface $traceable);

    /**
     * @param TraceableInterface $traceable
     * @param string             $action
     *
     * @return Trace[]
     */
    public function getAllTracesByObjectAndAction(TraceableInterface $traceable, string $action): array;
}
