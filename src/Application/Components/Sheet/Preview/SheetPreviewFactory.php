<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Preview;

use Proximum\Vimeet\Application\Components\Participant\ParticipantManager;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\Intl\Intl;

class SheetPreviewFactory
{
    /**
     * @var ParticipantManager
     */
    private $participantManager;

    /**
     * SheetPreviewFactory constructor.
     *
     * @param ParticipantManager $participantManager
     */
    public function __construct(ParticipantManager $participantManager)
    {
        $this->participantManager = $participantManager;
    }

    /**
     * @param Sheet  $sheet
     * @param User   $user
     * @param string $locale
     *
     * @return SheetPreview
     */
    public function createFromSheet(Sheet $sheet, User $user, $locale)
    {
        $steps = array_slice(array_keys($sheet->getType()->getPackageTemplate()), 0, 1, false);

        return new SheetPreview(
            $locale,
            $sheet->getId(),
            $sheet->getType()->getTitle($locale),
            $this->createParticipantView($sheet, $user, $locale),
            $this->participantManager->canAddParticipant($sheet),
            $this->createBlockViews($sheet, $user, $locale),
            $sheet->getOrders()->count(),
            current($steps)
        );
    }

    /**
     * @param Sheet  $sheet
     * @param User   $user
     * @param string $locale
     *
     * @return ParticipantDataView[]
     */
    private function createParticipantView(Sheet $sheet, User $user, $locale)
    {
        $template = $sheet->getType()->getParticipantTemplate();

        return array_map(function (Participant $participant) use ($template, $user, $locale) {
            $data     = $participant->getData();
            $rowViews = [];

            foreach ($template as $rowKey => $rowValue) {
                $rowViews[] = new RowDataView(
                    $rowValue['label'][$locale],
                    isset($data[$rowKey]) ? $data[$rowKey] : '...'
                );
            }

            return new ParticipantDataView(
                $participant->getId(),
                $participant->getUser()->getEmail(),
                $rowViews,
                $participant->isOwner(),
                $participant->getUser()->getId() === $user->getId() || $participant->isOwner()
            );
        }, $sheet->getParticipants()->toArray());
    }

    /**
     * @param Sheet  $sheet
     * @param User   $user
     * @param string $locale
     *
     * @return BlockDataView[]
     */
    private function createBlockViews(Sheet $sheet, User $user, $locale)
    {
        $data     = $sheet->getData();
        $template = $sheet->getType()->getSheetTemplate();

        $blocksViews = [];

        foreach ($template as $blockKey => $blockValue) {
            $rowViews = [];

            foreach ($blockValue['template'] as $rowKey => $rowValue) {
                $value = isset($data[$blockKey][$rowKey]) ? $data[$blockKey][$rowKey] : null;

                if ($value && isset($rowValue['choices'][$value]['label'][$locale])) {
                    $value = $rowValue['choices'][$value]['label'][$locale];
                }

                if ($value && $rowValue['type'] === 'lib_country') {
                    $value = Intl::getRegionBundle()->getCountryName($value, $locale);
                }

                $rowViews[$rowKey] = new RowDataView($rowValue['label'][$locale], $value ? $value : '...');
            }

            $blocksViews[$blockKey] = new BlockDataView($blockValue['label'][$locale], $rowViews);
        }

        return $blocksViews;
    }
}
