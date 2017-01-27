<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Spot\Batch;

use Proximum\Vimeet\Domain\Model\Spot;

class DeleteBatchView
{
    /**
     * @var Spot[]
     */
    public $deletedSpots;

    /**
     * @var Spot[]
     */
    public $spotsWithMeetings;

    /**
     * @var Spot[]
     */
    public $spotsWithSheets;

    /**
     * DeleteBatchView constructor.
     * @param Spot[] $deletedSpots
     * @param Spot[] $spotsWithMeetings
     * @param Spot[] $spotsWithSheets
     */
    public function __construct(array $deletedSpots = [], array $spotsWithMeetings = [], array $spotsWithSheets = [])
    {
        $this->deletedSpots = $deletedSpots;
        $this->spotsWithMeetings = $spotsWithMeetings;
        $this->spotsWithSheets = $spotsWithSheets;
    }

    /**
     * @param $spotsIds[]
     *
     * @return string
     */
    public function addError($spotsIds)
    {
        $errorString = '';
        foreach($spotsIds as $val) {
            $errorString.= $val . ' - ';
        }
        return rtrim($errorString , '- ');
    }
}
