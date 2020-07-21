<?php

namespace Proximum\Vimeet\Domain\Notification;

final class Notification
{
    /**
     * Priority
     */
    public const PRIORITY_REQUIRED  = 'required';
    public const PRIORITY_IMPORTANT = 'important';
    public const PRIORITY_NONE      = 'none';

    /**
     * Category
     */
    public const CATEGORY_SHEET       = 'sheet';
    public const CATEGORY_TRANSACTION = 'transaction';
    public const CATEGORY_PACKAGE     = 'package';

    /**
     * Notification Type
     */
    public const TYPE_SHEET_TRANSLATION_COMPLETENESS = 'sheetTranslationCompleteness';
    public const TYPE_PACKAGE_SELECTED               = 'packageSelected';
    public const TYPE_TRANSACTION_PENDING            = 'transactionPending';
}
