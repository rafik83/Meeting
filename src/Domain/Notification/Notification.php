<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
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
    const CATEGORY_SHEET = 'sheet';

    /**
     * Type
     */
    const TYPE_SHEET_TRANSLATION_COMPLETENESS = 'sheetTranslationCompleteness';
}
