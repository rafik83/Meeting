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
use FOS\ElasticaBundle\Exception\InvalidArgumentTypeException;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetElasticTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @param SheetInfoGuesser $sheetInfoGuesser
     */
    public function __construct(SheetInfoGuesser $sheetInfoGuesser)
    {
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * {@inheritDoc}
     */
    public function transform($sheet, array $fields)
    {
        $id = $sheet->getId();

        if ($sheet instanceof Sheet) {
            $sheetName         = $this->sheetInfoGuesser->guessSheetInfo($sheet);
            $state             = $sheet->getState();
            $type              = $sheet->getType()->getId();
            $categories        = array_map(function (Category $category) { return ['id' => $category->getId()]; }, $sheet->getType()->getCategories()->toArray());
            $followUp          = $sheet->getFollower() instanceof Admin ? $sheet->getFollower()->getId() : 0;
            $participantNumber = count($sheet->getParticipants());
            $event             = $sheet->getEvent()->getId();
            $createdAt         = $sheet->getCreatedAt()->format('c');

            try {
                $owner = $sheet->getOwner()->getId();
            } catch (\RuntimeException $e) {
                $owner = 0;
            }
        } else {
            throw new InvalidArgumentTypeException($sheet, 'Sheet');
        }

        return new Document($id, [
            'id'                => $id,
            'sheetName'         => $sheetName,
            'state'             => $state,
            'type'              => $type,
            'categories'        => $categories,
            'followUp'          => $followUp,
            'participantNumber' => $participantNumber,
            'event'             => $event,
            'owner'             => $owner,
            'createdAt'         => $createdAt,
        ]);
    }
}
