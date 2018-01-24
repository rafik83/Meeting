<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Sheet;

/**
 * Statut commercial
 */
class CommercialStatus
{
    /** Accord verbal */
    const STATUS_VERBAL_AGREEMENT = 'verbal_agreement';

    /** Annulation */
    const STATUS_CANCELED = 'canceled';

    /** Chaud */
    const STATUS_HOT = 'hot';

    /** Chaud stand */
    const STATUS_HOT_STALL = 'hot_stall';

    /** Inscrit */
    const STATUS_REGISTERED = 'registered';

    /** Intérêt */
    const STATUS_INTEREST = 'interest';

    /** Intérêt N+1 */
    const STATUS_INTEREST_NEXT_EDITION = 'interest_next_edition';

    /** Ne pas appeler */
    const STATUS_DO_NOT_CALL = 'do_not_call';

    /** Pas intéressé */
    const STATUS_NO_INTEREST = 'no_interest';

    /** Refus organisateur */
    const STATUS_REFUSED_BY_ORGANIZER = 'refused_by_organizer';

    /** Suivi */
    const STATUS_FOLLOWED = 'followed';

    const STATUS = [
        self::STATUS_VERBAL_AGREEMENT,
        self::STATUS_CANCELED,
        self::STATUS_HOT,
        self::STATUS_HOT_STALL,
        self::STATUS_REGISTERED,
        self::STATUS_INTEREST,
        self::STATUS_INTEREST_NEXT_EDITION,
        self::STATUS_DO_NOT_CALL,
        self::STATUS_NO_INTEREST,
        self::STATUS_REFUSED_BY_ORGANIZER,
        self::STATUS_FOLLOWED,
    ];

    const STATUS_WITH_LABEL = [
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
    ];
}
