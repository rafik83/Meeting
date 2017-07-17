<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Nomenclature\Import;

use Proximum\Vimeet\Application\Adapter\IntlInterface;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\ImportException;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\InvalidLocaleException;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\LocalesMustCorrespondToThoseOfTheEventException;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\Nomenclature\Id\IdGeneratorInterface;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\DepthException;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\FileNotFoundException;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\NoLocaleSpecifiedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;

class CsvImporter implements ImporterInterface
{
    /**
     * @var IdGeneratorInterface
     */
    private $generator;

    /**
     * @var IntlInterface
     */
    private $intl;

    /**
     * CsvImporter constructor.
     *
     * @param IdGeneratorInterface $generator
     * @param IntlInterface        $intl
     */
    public function __construct(IdGeneratorInterface $generator, IntlInterface $intl)
    {
        $this->generator = $generator;
        $this->intl = $intl;
    }

    /**
     * {@inheritdoc}
     */
    public function import(Nomenclature $nomenclature, $value, $charset)
    {
        $csv     = $this->parseFile($value, $charset);
        $locales = $this->parseLocales($csv);
        $this->validateLocales($nomenclature->getEvent(), $locales);

        $values = [];
        $depths = [];

        if (!is_array($csv)) {
            throw new ImportException();
        }

        foreach (array_slice($csv, 1) as $row) {

            if (!is_array($row)) {
                continue;
            }

            $id    = $this->getId($row);
            $depth = $this->getDepth($row);

            $depths[$depth] = $id;

            if ($depth === 1) {
                $values[$id] = $this->parseLabels($locales, $row, $depth);
            } elseif ($depth === 2) {
                if (!isset($depths[1])) {
                    throw new DepthException();
                }

                $values[$depths[1]]['children'][$id] = $this->parseLabels($locales, $row, $depth);
            } elseif ($depth === 3) {
                if (!isset($depths[1]) || !isset($depths[2])) {
                    throw new DepthException();
                }

                $values[$depths[1]]['children'][$depths[2]]['children'][$id] = $this->parseLabels($locales, $row, $depth);
            }
        }

        $depth = count($depths) > 0 ? max(array_keys($depths)) : 0;

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
     *
     * @throws ImportException
     * @throws InvalidLocaleException
     * @throws NoLocaleSpecifiedException
     */
    private function parseLocales(array $csv): array
    {
        if (!isset($csv[0]) || !is_array($csv[0])) {
            throw new ImportException();
        }

        $locales = $this->filterEmpty(array_slice($csv[0], 1));

        if (empty($locales)) {
            throw new NoLocaleSpecifiedException('No locale specified.');
        }

        $locales = array_map('trim', $locales);

        $validLocales = $this->intl->getLocales();

        foreach ($locales as &$locale) {
            $locale = trim($locale);

            if (!in_array($locale, $validLocales)) {
                throw new InvalidLocaleException($locale);
            }
        }

        return $locales;
    }

    /**
     * @param Event|null $event
     * @param array      $locales
     *
     * @throws LocalesMustCorrespondToThoseOfTheEventException
     */
    private function validateLocales(Event $event = null, array $locales)
    {
        if (null === $event) {
            return;
        }

        $eventLocales = $event->getLocales();
        sort($eventLocales);
        sort($locales);

        if ($eventLocales !== $locales) {
            throw new LocalesMustCorrespondToThoseOfTheEventException();
        }
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
     * @param string $charset
     *
     * @return array
     * @throws FileNotFoundException
     */
    private function parseFile($filename, $charset)
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new FileNotFoundException('File not found.');
        }

        $filename = Charset::convert($filename, $charset, Charset::UTF_8);
        $file     = new \SplFileObject($filename);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(';');

        return iterator_to_array($file);
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
