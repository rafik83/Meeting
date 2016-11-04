<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Sheet;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Notification\SheetCompletenessEvent;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\SheetCompleteness;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetCompletenessRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\ItemCollection;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class CompletenessCalculator
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var SheetCompletenessRepositoryInterface
     */
    private $sheetCompletenessRepository;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param TemplateDataFactory                  $templateDataFactory
     * @param SheetRepositoryInterface             $sheetRepository
     * @param SheetCompletenessRepositoryInterface $sheetCompletenessRepository
     * @param DelayedEventDispatcher               $eventDispatcher
     */
    public function __construct(
        TemplateDataFactory $templateDataFactory,
        SheetRepositoryInterface $sheetRepository,
        SheetCompletenessRepositoryInterface $sheetCompletenessRepository,
        DelayedEventDispatcher $eventDispatcher
    ) {

        $this->templateDataFactory         = $templateDataFactory;
        $this->sheetRepository             = $sheetRepository;
        $this->sheetCompletenessRepository = $sheetCompletenessRepository;
        $this->eventDispatcher             = $eventDispatcher;
    }

    /**
     * @param Sheet $sheet
     */
    public function calculateCompleteness(Sheet $sheet)
    {
        $locales  = $sheet->getEvent()->getLocales();
        $fallback = $sheet->getEvent()->getFallback();

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

        $this->calculateCompletenessOfParticipantProfile(
            $templateDataRegistration,
            $sheet->getParticipants()->toArray(),
            $total,
            $completed,
            $locales
        );

        $this->sheetCompletenessRepository->removeForSheet($sheet);

        $notificationCompleteness = [];
        $averageCompleteness = 0;
        foreach ($locales as $locale) {
            $localeCompleteness        = floor($completed[$locale] / $total[$locale] * 100);
            $unitLocalizedCompleteness = new SheetCompleteness(
                $sheet,
                $locale,
                $localeCompleteness
            );

            $this->sheetCompletenessRepository->add($unitLocalizedCompleteness);
            $averageCompleteness += $localeCompleteness;

            $notificationCompleteness[$locale] = $localeCompleteness === 100;
        }

        $sheet->setCompleteness(intval(floor($averageCompleteness / count($locales))));

        $this->sheetRepository->set($sheet);

        // trigger sheet completeness event to generate notification
        $this->eventDispatcher->dispatch(
            Events::SHEET_COMPLETENESS,
            new SheetCompletenessEvent($sheet, $notificationCompleteness)
        );
    }

    /**
     * @param TemplateData $templateData
     * @param array        $participants
     * @param array        $total
     * @param array        $completed
     * @param array        $locales
     */
    private function calculateCompletenessOfParticipantProfile(
        TemplateData $templateData,
        array $participants,
        array &$total,
        array &$completed,
        array $locales
    ) {
        $countCompleted = 0;
        $countTotal     = 0;
        foreach ($templateData->getProfileObjects() as $object) {
            if ($object->isEditable() && true === $object->getRequired()) {
                $countTotal++;

                /** @var Participant $participant */
                foreach ($participants as $participant) {
                    $data = $participant->getData();

                    if (!empty($data[$object->getKey()])) {
                        $countCompleted++;
                    }
                }

            }
        }

        foreach ($locales as $locale) {
            $total[$locale] += $countTotal * count($participants);
            $completed[$locale] += $countCompleted;
        }
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
    ) {
        $countTotal     = 0;
        $countCompleted = 0;
        foreach ($templateData->getCompanyObjects() as $object) {
            if ($object->isEditable() && true === $object->getRequired()) {
                $countTotal++;

                if (!empty($object->getContentValue())) {
                    $countCompleted++;
                }
            }
        }

        foreach ($locales as $locale) {
            $total[$locale] += $countTotal;
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
    ) {
        foreach ($templateData->getObjects() as $object) {
            if (true === $object->getRequired()) {
                if ($object->isEditable() && $object instanceof ContentObjectInterface) {
                    if ($object->isTranslatable()) {
                        foreach ($locales as $locale) {
                            $total[$locale]++;
                            $data = $object->getContentValueLocalize($locale);

                            if (!empty($data)) {
                                $completed[$locale]++;
                            }
                        }
                    } else {
                        $data = $object->getContentValue();

                        foreach ($locales as $locale) {
                            $total[$locale]++;
                            if (!empty($data)) {
                                $completed[$locale]++;
                            }
                        }
                    }
                } elseif ($object instanceof ItemCollection) {
                    $count = count($object->getItems());

                    // In case of a required ItemCollection with no item
                    if ($count === 0) {
                        $count = 1;
                    }

                    foreach ($locales as $locale) {
                        $total[$locale] += $count;
                        foreach ($object->getItems() as $item) {
                            if (isset($item->getRawTitle()[$locale])
                                && $item->getRawTitle()[$locale] !== null
                            ) {
                                $completed[$locale]++;
                            }
                        }
                    }
                }
            }
        }
    }
}
