<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Application\Components\Participant\ParticipantManager;
use Proximum\Vimeet\Application\Components\Sheet\Block\BlockDataViewFactory;
use Proximum\Vimeet\Application\Components\Sheet\Block\RowDataView;
use Proximum\Vimeet\Application\Components\Template\TemplateFactory;
use Proximum\Vimeet\Application\View\Sheet\Preview\ParticipantDataView;
use Proximum\Vimeet\Application\View\Sheet\Preview\SheetPreview;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SheetPreviewQueryHandler
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
     * @param SheetPreviewQuery $query
     *
     * @return SheetPreview
     */
    public function handle(SheetPreviewQuery $query)
    {
        $steps = array_slice(array_keys($query->sheet->getType()->getPackageTemplate()), 0, 1, false);

        return new SheetPreview(
            $query->locale,
            $query->sheet->getId(),
            $query->sheet->getType()->getTitle($query->locale),
            $this->createParticipantViews($query->sheet, $query->user, $query->locale),
            $this->participantManager->canAddParticipant($query->sheet),
            $this->blockDataViewFactory->createBlockViews($query->sheet, $query->locale),
            $query->sheet->getOrders()->count(),
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
