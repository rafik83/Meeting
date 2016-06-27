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
    public function import(Nomenclature $nomenclature, $value)
    {
        $csv   = array_map(function ($line) { return str_getcsv($line, ';'); }, file($value));
        $value = [];

        $locales = $this->getLocales($csv);

        $pointers = [];

        foreach (array_slice($csv, 1) as $row) {

            $id    = $this->getId($row);
            $depth = $this->getDepth($row);

            $pointers[$depth] = $id;
            
            if ($depth === 1) {
                $value[$id] = [
                    'label' => $this->getLabels($locales, $row, $depth),
                ];
            } elseif ($depth === 2) {
                $value[$pointers[1]]['children'][$id] = [
                    'label' => $this->getLabels($locales, $row, $depth),
                ];
            } elseif ($depth === 3) {
                $value[$pointers[1]]['children'][$pointers[2]]['children'][$id] = [
                    'label' => $this->getLabels($locales, $row, $depth),
                ];
            }
        }

        $nomenclature->update(max(array_keys($pointers)), $value);
    }

    /**
     * @param array $row
     *
     * @return int
     */
    private function getDepth(array &$row)
    {
        $depth = 1;

        foreach (array_slice($row, 1) as $columns) {
            if (empty($columns)) {
                $depth++;
            } else {
                return $depth;
            }
        }

        return $depth;
    }

    /**
     * @param array $row
     *
     * @return string
     */
    private function getId(array &$row)
    {
        return empty($row[0]) ? $this->generator->generate() : $row[0];
    }

    /**
     * @param array $csv
     *
     * @return array
     */
    private function getLocales(array $csv)
    {
        return $this->filterEmpty(array_slice($csv[0], 1));
    }

    /**
     * @param array $array
     *
     * @return array
     */
    private function filterEmpty(array $array)
    {
        return array_filter($array, function ($value) {
            return !empty($value);
        });
    }

    /**
     * @param array $locales
     * @param array $row
     * @param int   $depth
     *
     * @return array
     */
    protected function getLabels($locales, $row, $depth)
    {
        return array_combine($locales, array_map('trim', array_slice($row, $depth, count($locales))));
    }
}
