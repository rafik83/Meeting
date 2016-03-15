<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Preview;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Application\Components\Participant\ParticipantManager;
use Proximum\Vimeet\Application\Components\Sheet\Block\BlockDataViewFactory;
use Proximum\Vimeet\Application\Components\Sheet\Block\RowDataView;
use Proximum\Vimeet\Application\Components\Template\TemplateFactory;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SheetPreviewFactory
{
    /**
     * @var ParticipantManager
     */
    private $participantManager;

    /**
     * @var BlockDataViewFactory
     */
    private $blockDataViewFactory;

    /**
     * SheetPreviewFactory constructor.
     *
     * @param ParticipantManager   $participantManager
     * @param BlockDataViewFactory $blockDataViewFactory
     */
    public function __construct(ParticipantManager $participantManager, BlockDataViewFactory $blockDataViewFactory)
    {
        $this->participantManager   = $participantManager;
        $this->blockDataViewFactory = $blockDataViewFactory;
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
            $this->createParticipantViews($sheet, $user, $locale),
            $this->participantManager->canAddParticipant($sheet),
            $this->blockDataViewFactory->createBlockViews($sheet, $locale),
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
    private function createParticipantViews(Sheet $sheet, User $user, $locale)
    {
        $template = (new TemplateFactory())->createTemplateFromArray($sheet->getType()->getParticipantTemplate());

        return array_map(function (Participant $participant) use ($template, $sheet, $user, $locale) {
            $data  = new ArrayCollection($participant->getData());
            $views = [];

            foreach ($template->getRows() as $key => $row) {

                // Don't add private data
                if ($row->isPrivate()) {
                    continue;
                }

                $views[] = new RowDataView(
                    $row->getLabel($locale),
                    $row->getDisplayableValue($data->get($key), $locale) ? : '...'
                );
            }

            return new ParticipantDataView(
                $participant->getId(),
                $participant->getUser()->getEmail(),
                $views,
                $participant->isOwner(),
                $this->participantManager->isUserAllowedToEditParticipant($sheet, $participant, $user),
                $this->participantManager->isUserAllowedToDeleteParticipant($sheet, $participant, $user)
            );

        }, $sheet->getParticipants()->toArray());
    }
}
