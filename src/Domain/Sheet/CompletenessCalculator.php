<?php

namespace Proximum\Vimeet\Domain\Sheet;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Notification\SheetCompletenessEvent;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\SheetCompleteness;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetCompletenessRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\BooleanObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\ItemCollection;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadCollectionObject;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class CompletenessCalculator
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetCompletenessRepositoryInterface */
    private $sheetCompletenessRepository;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    public function __construct(
        TemplateDataFactory $templateDataFactory,
        SheetRepositoryInterface $sheetRepository,
        SheetCompletenessRepositoryInterface $sheetCompletenessRepository,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->sheetRepository = $sheetRepository;
        $this->sheetCompletenessRepository = $sheetCompletenessRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function calculateCompleteness(Sheet $sheet): void
    {
        $locales= $sheet->getEvent()->getLocales();
        $fallback = $sheet->getEvent()->getLocaleFallback();

        $total     = [];
        $completed = [];

        foreach ($locales as $locale) {
            $total[$locale]     = 0;
            $completed[$locale] = 0;
        }

        // Build template for sheet
        $templateData = $this->templateDataFactory->createFromSheet($sheet, $fallback);
        $this->calculateCompletenessOfSheet($templateData, $locales, $total, $completed);

        // Build template for company info
        $templateDataRegistration = $this->templateDataFactory->createRegistrationFromSheet($sheet, $fallback);
        $this->calculateCompletenessOfCompanyInfo($templateDataRegistration, $total, $completed, $locales);

        $this->sheetCompletenessRepository->removeForSheet($sheet);

        $notificationCompleteness = [];
        $averageCompleteness      = 0;
        foreach ($locales as $locale) {
            $localeCompleteness = 0 !== $total[$locale] ? floor($completed[$locale] / $total[$locale] * 100) : 0;
            $unitLocalizedCompleteness = new SheetCompleteness(
                $sheet,
                $locale,
                $localeCompleteness
            );

            $this->sheetCompletenessRepository->add($unitLocalizedCompleteness);
            $averageCompleteness += $localeCompleteness;

            $notificationCompleteness[$locale] = 100 === (int) $localeCompleteness;
        }

        $sheet->setCompleteness((int) (floor($averageCompleteness / count($locales))));

        $this->sheetRepository->set($sheet);

        // trigger sheet completeness event to generate notification
        $this->eventDispatcher->dispatch(
            Events::SHEET_COMPLETENESS,
            new SheetCompletenessEvent($sheet, $notificationCompleteness)
        );
    }

    /**
     * @param TemplateData $templateData
     * @param array        $total
     * @param array        $completed
     * @param array        $locales
     */
    private function calculateCompletenessOfCompanyInfo(
        TemplateData $templateData,
        array &$total,
        array &$completed,
        array $locales
    ): void {
        $countTotal     = 0;
        $countCompleted = 0;
        foreach ($templateData->getEditableSheetDataExceptedImageObjects() as $object) {
            if ($object->isEditable() && true === $object->getRequired()) {
                ++$countTotal;

                // necessary when data is equal to false (!empty(false) = false)
                if ($object instanceof BooleanObject && null !== $object->getContentValue()) {
                    ++$countCompleted;
                } elseif (!empty($object->getContentValue())) {
                    ++$countCompleted;
                }
            }
        }

        foreach ($locales as $locale) {
            $total[$locale]     += $countTotal;
            $completed[$locale] += $countCompleted;
        }
    }

    /**
     * @param TemplateData $templateData
     * @param array        $locales
     * @param array        $total
     * @param array        $completed
     */
    private function calculateCompletenessOfSheet(
        TemplateData $templateData,
        array $locales,
        array &$total,
        array &$completed
    ): void {
        foreach ($templateData->getObjects() as $object) {
            if (true === $object->getRequired()) {
                if ($object->isEditable() && $object instanceof ContentObjectInterface) {
                    if ($object->isTranslatable()) {
                        foreach ($locales as $locale) {
                            ++$total[$locale];
                            $data = $object->getContentValueLocalize($locale);

                            // necessary when data is equal to false (!empty(false) = false)
                            if ($object instanceof BooleanObject && null !== $data) {
                                ++$completed[$locale];
                            } elseif (!empty($data)) {
                                ++$completed[$locale];
                            }
                        }
                    } else {
                        $data = $object->getContentValue();

                        foreach ($locales as $locale) {
                            ++$total[$locale];
                            if (!empty($data)) {
                                ++$completed[$locale];
                            }
                        }
                    }
                } elseif ($object instanceof ItemCollection) {
                    $count = \count($object->getItems());

                    // In case of a required ItemCollection with no item
                    if (0 === $count) {
                        foreach ($locales as $locale) {
                            ++$total[$locale];
                        }
                    } else {
                        foreach ($locales as $locale) {
                            ++$total[$locale];

                            foreach ($object->getItems() as $item) {
                                if (!empty($item->getTitleLocalize($locale))) {
                                    ++$completed[$locale];

                                    break;
                                }
                            }
                        }
                    }
                } elseif ($object instanceof MultiUploadCollectionObject) {
                    $count = \count($object->getUploads());

                    if (0 === $count) {
                        foreach ($locales as $locale) {
                            ++$total[$locale];
                        }
                    } else {
                        foreach ($locales as $locale) {
                            ++$total[$locale];

                            foreach ($object->getUploads() as $upload) {
                                if ($upload->getPath()) {
                                    ++$completed[$locale];

                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
