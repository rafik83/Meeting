<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Nomenclature;

use Proximum\Vimeet\Application\Nomenclature\Id\IdGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Nomenclature;

class CsvImporter implements ImporterInterface
{
    /**
     * @var IdGeneratorInterface
     */
    private $generator;

    /**
     * CsvImporter constructor.
     *
     * @param IdGeneratorInterface $generator
     */
    public function __construct(IdGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function import($title, $value)
    {
        $csv   = array_map(function ($line) { return str_getcsv($line, ';'); }, file($value));
        $value = [];

        $locales = $this->getLocales($csv);

        foreach (array_slice($csv, 1) as $row) {

            $id   = $this->getId($row);
            $deep = $this->getDeep($row);

            if ($deep === 1) {
                $value[$id] = [
                    'label' => array_combine($locales, array_slice($row, $deep, count($locales))),
                ];
            }

        }

        return new Nomenclature($title, 1, $value);
    }

    private function getDeep(array &$row)
    {
        $deep = 1;

        foreach (array_slice($row, 1) as $columns) {
            if (empty($columns)) {
                $deep++;
            } else {
                return $deep;
            }
        }

        return $deep;
    }

    private function getId(array &$row)
    {
        return empty($row[0]) ? $this->generator->generate() : $row[0];
    }

    private function getLocales(array $csv)
    {
        return $this->filterEmpty(array_slice($csv[0], 1));
    }

    private function filterEmpty(array $array)
    {
        return array_filter($array, function ($value) {
            return !empty($value);
        });
    }
}
