<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Transformer\Elastic;

use Elastica\Document;
use FOS\ElasticaBundle\Exception\InvalidArgumentTypeException;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
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
            $sheetName = $this->sheetInfoGuesser->guessSheetInfo($sheet);
        } else {
            throw new InvalidArgumentTypeException($sheet, 'Sheet');
        }

        return new Document($id, [
            'id'        => $id,
            'sheetName' => $sheetName,
        ]);
    }
}
