<?php

namespace Proximum\Vimeet\Application\Query\Notification\Sheet;

use Proximum\Vimeet\Application\View\Notification\NotificationView;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetCompletenessRepositoryInterface;

class SheetNotificationViewQueryHandler
{
    /** @var SheetCompletenessRepositoryInterface */
    private $sheetCompletenessRepository;

    /** @var CompleteTranslationViewQueryHandler */
    private $completeTranslationViewQueryHandler;

    public function __construct(
        CompleteTranslationViewQueryHandler $completeTranslationViewQueryHandler,
        SheetCompletenessRepositoryInterface $sheetCompletenessRepository
    ) {
        $this->sheetCompletenessRepository         = $sheetCompletenessRepository;
        $this->completeTranslationViewQueryHandler = $completeTranslationViewQueryHandler;
    }

    /**
     * @param SheetNotificationViewQuery $query
     *
     * @return NotificationView[]
     */
    public function handle(SheetNotificationViewQuery $query): array
    {
        $notificationViews = [];

        $locales = $query->sheet->getEvent()->getLocales();

        foreach ($locales as $locale) {
            $sheetCompleteness = $this->sheetCompletenessRepository->findCompleteness($query->sheet, $locale);

            if (null !== $sheetCompleteness && 100 !== $sheetCompleteness->getCompleteness()) {
                $notificationViews[] = $this->completeTranslationViewQueryHandler->handle(
                    new CompleteTranslationViewQuery($query->sheet, $locale)
                );
            }
        }

        return $notificationViews;
    }
}
