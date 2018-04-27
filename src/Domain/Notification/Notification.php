<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Notification;

final class Notification
{
    /**
     * Priority
     */
    const PRIORITY_REQUIRED  = 'required';
    const PRIORITY_IMPORTANT = 'important';
    const PRIORITY_NONE      = 'none';

    /**
     * Category
     */
    const CATEGORY_SHEET       = 'sheet';
    const CATEGORY_TRANSACTION = 'transaction';
    const CATEGORY_PACKAGE     = 'package';

    /**
     * Notification Type
     */
    const TYPE_SHEET_TRANSLATION_COMPLETENESS = 'sheetTranslationCompleteness';
    const TYPE_PACKAGE_SELECTED               = 'packageSelected';
    const TYPE_TRANSACTION_PENDING            = 'transactionPending';
}
