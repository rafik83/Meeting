<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Meeting;

final class Constant
{
    const FILTER_STATE_SENT     = 'sent';
    const FILTER_STATE_APPROVED = 'approved';
    const FILTER_STATE_REFUSED  = 'refused';
    const FILTER_STATE_RECEIVE  = 'receive';
    const FILTER_STATE_ALL      = 'all';

    const FILTER_AVAILABLE_SLOT_IDS_EVERYONE  = 'everyone';
    const FILTER_AVAILABLE_SLOT_IDS_AVAILABLE = 'available';
    const FILTER_AVAILABLE_SLOT_IDS_SLOT      = 'slot';

    /**
     * @return array
     */
    public static function getAllStates()
    {
        return [
            self::FILTER_STATE_ALL,
            self::FILTER_STATE_RECEIVE,
            self::FILTER_STATE_SENT,
            self::FILTER_STATE_APPROVED,
            self::FILTER_STATE_REFUSED,
        ];
    }

    /**
     * @param $filter
     *
     * @return bool
     */
    public static function isSentOrReceiveFilter($filter)
    {
        return in_array($filter, [
            self::FILTER_STATE_RECEIVE,
            self::FILTER_STATE_SENT,
        ]);
    }

    /**
     * @param $filter
     *
     * @return null|string
     */
    public static function getMappedRequestState($filter)
    {
        switch ($filter) {
            case self::FILTER_STATE_SENT:
                return Request::STATE_SENT;
            case self::FILTER_STATE_APPROVED:
                return Request::STATE_APPROVED;
            case self::FILTER_STATE_REFUSED:
                return Request::STATE_REFUSED;
        }

        return null;
    }
}
