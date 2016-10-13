<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Meeting;

final class Constant
{
    const STATE_SENT     = 'sent';
    const STATE_APPROVED = 'approved';
    const STATE_REFUSED  = 'refused';
    const STATE_CANCEL   = 'cancelled';
    const STATE_RECEIVE  = 'receive';
    const STATE_ALL      = 'all';

    /**
     * @return array
     */
    public static function getAllStates()
    {
        return [
            self::STATE_ALL,
            self::STATE_RECEIVE,
            self::STATE_SENT,
            self::STATE_APPROVED,
            self::STATE_REFUSED,
        ];
    }
}
