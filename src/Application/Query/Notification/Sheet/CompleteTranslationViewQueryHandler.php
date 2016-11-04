<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Notification\Sheet;

use Proximum\Vimeet\Application\Query\Notification\AbstractNotificationQueryHandler;
use Proximum\Vimeet\Application\View\Notification\NotificationView;
use Proximum\Vimeet\Domain\Notification\Notification;

class CompleteTranslationViewQueryHandler extends AbstractNotificationQueryHandler
{
    /**
     * @param CompleteTranslationViewQuery $query
     *
     * @return NotificationView
     */
    public function handle(CompleteTranslationViewQuery $query)
    {
        return new NotificationView(
            $this->datetime,
            Notification::CATEGORY_SHEET,
            'notification.sheet.completeTranslation',
            $this->router->generateSheet($query->sheet),
            Notification::PRIORITY_REQUIRED
        );
    }
}
