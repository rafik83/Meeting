<?php

namespace Proximum\Vimeet\Domain\Sheet;

/**
 * Statut commercial
 */
class CommercialStatus
{
    /** Aucun */
    public const STATUS_NONE = 'none';

    /** Accord verbal */
    public const STATUS_VERBAL_AGREEMENT = 'verbal_agreement';

    /** Annulation */
    public const STATUS_CANCELED = 'canceled';

    /** Chaud */
    public const STATUS_HOT = 'hot';

    /** Chaud stand */
    public const STATUS_HOT_STALL = 'hot_stall';

    /** Inscrit */
    public const STATUS_REGISTERED = 'registered';

    /** Intérêt */
    public const STATUS_INTEREST = 'interest';

    /** Intérêt N+1 */
    public const STATUS_INTEREST_NEXT_EDITION = 'interest_next_edition';

    /** Ne pas appeler */
    public const STATUS_DO_NOT_CALL = 'do_not_call';

    /** Pas intéressé */
    public const STATUS_NO_INTEREST = 'no_interest';

    /** Refus organisateur */
    public const STATUS_REFUSED_BY_ORGANIZER = 'refused_by_organizer';

    /** Suivi */
    public const STATUS_FOLLOWED = 'followed';

    /** Log Ok */
    public const STATUS_LOGISTIC_OK = 'log_ok';

    /** Log sans billet */
    public const STATUS_LOGISTIC_WITHOUT_TICKET = 'log_without_ticket';

    /** Log Pas Ok */
    public const STATUS_LOGISTIC_NOT_OK = 'log_not_ok';

    /** Présentation incomplète */
    public const STATUS_INCOMPLETE_PRESENTATION = 'incomplete_presentation';

    public const STATUS = [
        self::STATUS_NONE,
        self::STATUS_VERBAL_AGREEMENT,
        self::STATUS_CANCELED,
        self::STATUS_HOT,
        self::STATUS_HOT_STALL,
        self::STATUS_REGISTERED,
        self::STATUS_INTEREST,
        self::STATUS_INTEREST_NEXT_EDITION,
        self::STATUS_LOGISTIC_OK,
        self::STATUS_LOGISTIC_NOT_OK,
        self::STATUS_LOGISTIC_WITHOUT_TICKET,
        self::STATUS_DO_NOT_CALL,
        self::STATUS_NO_INTEREST,
        self::STATUS_INCOMPLETE_PRESENTATION,
        self::STATUS_REFUSED_BY_ORGANIZER,
        self::STATUS_FOLLOWED,
    ];

    public const STATUS_WITH_LABEL = [
        self::STATUS_NONE => 'default',
        self::STATUS_VERBAL_AGREEMENT => 'success',
        self::STATUS_CANCELED => 'danger',
        self::STATUS_HOT => 'info',
        self::STATUS_HOT_STALL => 'info',
        self::STATUS_REGISTERED => 'success',
        self::STATUS_INTEREST => 'info',
        self::STATUS_INTEREST_NEXT_EDITION => 'danger',
        self::STATUS_DO_NOT_CALL => 'danger',
        self::STATUS_NO_INTEREST => 'danger',
        self::STATUS_REFUSED_BY_ORGANIZER => 'danger',
        self::STATUS_FOLLOWED => 'info',
        self::STATUS_LOGISTIC_NOT_OK => 'danger',
        self::STATUS_LOGISTIC_WITHOUT_TICKET => 'info',
        self::STATUS_LOGISTIC_OK => 'success',
        self::STATUS_INCOMPLETE_PRESENTATION => 'danger',
    ];
}
