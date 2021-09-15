<?php

namespace Proximum\Vimeet\Domain\Model\Meeting;

final class Constant
{
    public const FILTER_STATE_SENT = 'sent';
    public const FILTER_STATE_APPROVED = 'approved';
    public const FILTER_STATE_REFUSED = 'refused';
    public const FILTER_STATE_RECEIVE = 'receive';
    public const FILTER_STATE_ALL = 'all';

    public const FILTER_AVAILABLE_SLOT_IDS_EVERYONE = 'everyone';
    public const FILTER_AVAILABLE_SLOT_IDS_AVAILABLE = 'available';
    public const FILTER_AVAILABLE_SLOT_IDS_SLOT = 'slot';

    public const FILTER_SHEET_VISIT_ALL = 'all';
    public const FILTER_SHEET_VISIT_SAW = 'sheetSaw';
    public const FILTER_SHEET_VISIT_VIEWED_BY = 'viewedBySheet';

    public static function getAllSheetVisitChoices(): array
    {
        return [
            self::FILTER_SHEET_VISIT_ALL,
            self::FILTER_SHEET_VISIT_SAW,
            self::FILTER_SHEET_VISIT_VIEWED_BY,
        ];
    }

    /**
     * @return array
     */
    public static function getAllStates(): array
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
    public static function isSentOrReceiveFilter($filter): bool
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
    public static function getMappedRequestState($filter): ?string
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
