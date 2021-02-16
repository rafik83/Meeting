<?php

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Model\Sheet;

class CatalogOnlineDateSubstitution implements SubstituteInterface
{
    /**
     * {@inheritdoc}
     */
    public function getValue(Sheet $sheet, $locale)
    {
        $event         = $sheet->getEvent();
        $dateFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            $event->getTimeZone()
        );

        $catalogOnlineDate = $event->getConfiguration()->getCatalogOnlineDate();

        if (null !== $catalogOnlineDate) {
            $formattedDate = $dateFormatter->format($catalogOnlineDate);
        }

        return isset($formattedDate) && false !== $formattedDate ? $formattedDate : '';
    }
}
