<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Decoder;

use Proximum\Vimeet\Application\Serializer\Decoder\Exception\EmptyFileException;
use SplFileObject;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class CsvDecoder implements DecoderInterface
{
    const FORMAT = 'csv';

    /**
     * @var string
     */
    private $delimiter;

    /**
     * CsvDecoder constructor.
     *
     * @param string $delimiter
     */
    public function __construct($delimiter = ';')
    {
        $this->delimiter = $delimiter;
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = [])
    {
        $file = new \SplFileObject($data);
        $file->setFlags(SplFileObject::READ_CSV);
        $file->setCsvControl($this->delimiter);

        $csvData = [];

        while ($file->eof() === false) {
            $csvData[] = $file->fgetcsv();
        }

        array_pop($csvData); // remove last empty line

        return array_slice($csvData, 1); // first line (headers)
    }

    /**
     * @param $filename
     *
     * @return array
     * @throws EmptyFileException
     */
    public function decodeHeaders($filename)
    {
        $file = new \SplFileObject($filename);
        $file->setFlags(SplFileObject::READ_CSV);
        $file->setCsvControl($this->delimiter);

        if ($file->eof() === true) {
            throw new EmptyFileException();
        }

        return $file->fgetcsv();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return $format === self::FORMAT;
    }
}
