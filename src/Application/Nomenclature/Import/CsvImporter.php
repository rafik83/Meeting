<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Nomenclature\Import;

use Proximum\Vimeet\Application\Nomenclature\Id\IdGeneratorInterface;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\DepthException;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\EmptyOrMalformedFileException;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\FileNotFoundException;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\NoLocaleSpecifiedException;
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
        $csv     = $this->parseFile($value);
        $locales = $this->parseLocales($csv);
        $values  = [];
        $depths  = [];

        foreach (array_slice($csv, 1) as $row) {

            $id    = $this->getId($row);
            $depth = $this->getDepth($row);

            $depths[$depth] = $id;

            if ($depth === 1) {
                $values[$id] = $this->parseLabels($locales, $row, $depth);
            } elseif ($depth === 2) {
                $values[$depths[1]]['children'][$id] = $this->parseLabels($locales, $row, $depth);
            } elseif ($depth === 3) {
                $values[$depths[1]]['children'][$depths[2]]['children'][$id] = $this->parseLabels($locales, $row, $depth);
            }
        }

        $depth = max(array_keys($depths));

        $this->checkDepth($values, $depth);

        $nomenclature->update($depth, $values);
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
     * @throws NoLocaleSpecifiedException
     */
    private function parseLocales(array $csv)
    {
        $locales = $this->filterEmpty(array_slice($csv[0], 1));

        if (empty($locales)) {
            throw new NoLocaleSpecifiedException('No locale specified.');
        }

        return $locales;
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
    private function parseLabels($locales, $row, $depth)
    {
        return [
            'label' => array_combine($locales, array_map('trim', array_slice($row, $depth, count($locales)))),
        ];
    }

    /**
     * @param string $filename
     *
     * @return array
     * @throws EmptyOrMalformedFileException
     * @throws FileNotFoundException
     */
    private function parseFile($filename)
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new FileNotFoundException('File not found.');
        }

        // Parse csv to array
        $csv = array_map(function ($line) {
            return str_getcsv($line, ';');
        }, file($filename));

        // Filter empty line
        $csv = array_filter($csv, function ($line) {
            return count($this->filterEmpty($line)) > 0;
        });

        if (empty($csv)) {
            throw new EmptyOrMalformedFileException('Empty or malformed file.');
        }

        return $csv;
    }

    /**
     * @param array $values
     * @param int   $depth
     *
     * @throws DepthException
     */
    private function checkDepth(array $values, $depth)
    {
        foreach ($values as $value) {
            if ($depth > 1 && !isset($value['children'])) {
                throw new DepthException('Missing children.');
            }

            if (isset($value['children'])) {
                $this->checkDepth($value['children'], $depth - 1);
            }
        }
    }
}
