<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Elastica\Transformer;

use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class SheetElasticTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @param SheetInfoGuesser       $sheetInfoGuesser
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(SheetInfoGuesser $sheetInfoGuesser, ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param Sheet $sheet
     * @param array $fields
     *
     * @return Document
     */
    public function transform($sheet, array $fields)
    {
        $locale = $sheet->getEvent()->getFallback();

        $participants = [];

        if (null !== $sheet->getParticipants()) {
            $participants = array_map(
                function (Participant $participant) use ($locale) {
                    return [
                        'email'    => $participant->getUser()->getEmail(),
                        'lastname' => $this->participantInfoGuesser->guessParticipantLastName(
                            $participant,
                            $locale
                        ),
                    ];
                },
                $sheet->getParticipants()->toArray()
            );

            if ($sheet->hasUserParticipant($sheet->getOwner())) {
                $participants[] = [
                    'email'    => $sheet->getOwner()->getEmail(),
                    'lastname' => $sheet->getOwner()->getAccount()->getLastName(),
                ];
            }
        }

        try {
            $owner = $sheet->getOwner()->getId();
        } catch (\RuntimeException $e) {
            $owner = null;
        }

        $categories = array_map(
            function (Category $category) {
                return ['id' => $category->getId()];
            },
            $sheet->getType()->getCategories()->toArray()
        );

        return new Document($sheet->getId(), [
            'id'                => $sheet->getId(),
            'sheetName'         => $this->sheetInfoGuesser->guessSheetName($sheet, $locale),
            'state'             => $sheet->getState(),
            'type'              => $sheet->getType()->getId(),
            'categories'        => $categories,
            'followUp'          => $sheet->getFollower() instanceof Admin ? $sheet->getFollower()->getId() : null,
            'participantNumber' => count($sheet->getParticipants()),
            'participants'      => $participants,
            'event'             => $sheet->getEvent()->getId(),
            'owner'             => $owner,
            'createdAt'         => $sheet->getCreatedAt()->format('c'),
        ]);
    }
}
