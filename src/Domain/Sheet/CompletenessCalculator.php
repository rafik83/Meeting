<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Sheet;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\ItemCollection;

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
     * @param TemplateDataFactory      $templateDataFactory
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(
        TemplateDataFactory $templateDataFactory,
        SheetRepositoryInterface $sheetRepository
    ) {

        $this->templateDataFactory = $templateDataFactory;
        $this->sheetRepository     = $sheetRepository;
    }

    /**
     * @param Sheet $sheet
     */
    public function calculateCompleteness(Sheet $sheet)
    {
        $total     = 0;
        $completed = 0;
        $locales   = $sheet->getEvent()->getLocales();
        $fallback  = $sheet->getEvent()->getFallback();

        // Build template for sheet
        $templateData = $this->templateDataFactory->createFromSheet($sheet, $fallback);
        $this->calculateCompletenessOfSheet($templateData, $locales, $total, $completed);

        // Build template for company info
        $templateDataRegistration = $this->templateDataFactory->createRegistrationFromSheet($sheet, $fallback);
        $this->calculateCompletenessOfCompanyInfo($templateDataRegistration, $total, $completed);

        $this->calculateCompletenessOfParticipantProfile(
            $templateDataRegistration,
            $sheet->getParticipants()->toArray(),
            $total,
            $completed
        );

        $completeness = $completed / $total * 100;
        $sheet->setCompleteness(floor($completeness));

        $this->sheetRepository->set($sheet);
    }

    /**
     * @param TemplateData $templateData
     * @param array        $participants
     * @param int          $total
     * @param int          $completed
     */
    private function calculateCompletenessOfParticipantProfile(
        TemplateData $templateData,
        array $participants,
        &$total,
        &$completed
    ) {
        foreach ($templateData->getProfileObjects() as $object) {
            if ($object->isEditable() && true === $object->getRequired()) {
                $total += count($participants);

                /** @var Participant $participant */
                foreach ($participants as $participant) {
                    $data = $participant->getData();
                    if (isset($data[$object->getKey()]) && null !== $data[$object->getKey()]) {
                        $completed++;
                    }
                }
            }
        }
    }

    /**
     * @param TemplateData $templateData
     * @param int          $total
     * @param int          $completed
     */
    private function calculateCompletenessOfCompanyInfo(TemplateData $templateData, &$total, &$completed)
    {
        foreach ($templateData->getCompanyObjects() as $object) {
            if ($object->isEditable() && true === $object->getRequired()) {
                $total++;

                if ($object->getContentValue() !== null) {
                    $completed++;
                }
            }
        }
    }

    /**
     * @param TemplateData $templateData
     * @param array        $locales
     * @param int          $total
     * @param int          $completed
     */
    private function calculateCompletenessOfSheet(TemplateData $templateData, array $locales, &$total, &$completed)
    {
        $localesCount = count($locales);

        foreach ($templateData->getObjects() as $object) {
            if (true === $object->getRequired()) {
                if ($object->isEditable() && $object instanceof ContentObjectInterface) {
                    if ($object->isTranslatable()) {
                        $total += $localesCount;

                        foreach ($locales as $locale) {
                            if ($object instanceof EditableText) {
                                $data = $object->getContent($locale);
                            } else {
                                $data = $object->getData();
                            }

                            if (null !== $data) {
                                $completed++;
                            }
                        }
                    } else {
                        $total++;
                        $data = $object->getContentValue();

                        if (null !== $data) {
                            $completed++;
                        }
                    }
                } elseif ($object instanceof ItemCollection) {
                    $count = count($object->getItems());

                    // In case of a required ItemCollection with no item
                    if ($count === 0) {
                        $count = 1;
                    }

                    if ($object->isTranslatable()) {
                        $total += $count * $localesCount;

                        foreach ($locales as $locale) {
                            foreach ($object->getItems() as $item) {
                                if (isset($item->getRawTitle()[$locale]) && $item->getRawTitle()[$locale] !== null) {
                                    $completed++;
                                }
                            }
                        }
                    } else {
                        $total += $count * $localesCount;

                        foreach ($object->getItems() as $item) {
                            if (null !== $item->getTitle()) {
                                $completed++;
                            }
                        }
                    }
                }
            }
        }
    }
}
