<?php

namespace Proximum\Vimeet\Application\Query\Notification\Sheet;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Notification\AbstractNotificationQueryHandler;
use Proximum\Vimeet\Application\View\Notification\NotificationView;
use Proximum\Vimeet\Domain\Notification\Notification;

class CompleteTranslationViewQueryHandler extends AbstractNotificationQueryHandler
{
    public function handle(CompleteTranslationViewQuery $query): NotificationView
    {
        $link = $this->router->generate(
            'event_sheet_locale',
            ['sheet' => $query->sheet->getId(), 'locale' => $query->locale]
        );

        return new NotificationView(
            $query->sheet->getCreatedAt(),
            Category::SHEET_ICON,
            Notification::CATEGORY_SHEET,
            'notification.sheet.completeTranslation',
            $link,
            Notification::PRIORITY_REQUIRED,
            ['%locale%' => $query->locale]
        );
    }
}
