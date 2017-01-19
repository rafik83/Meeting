<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * CSV encoder meant to be used as part of Symfony's {Serializer component @link http://symfony.com/doc/current/serializer.html}.
 *
 * @deprecated When CsvEncoder is available in Symfony 3.2
 *
 * @link http://symfony.com/blog/new-in-symfony-3-2-csv-and-yaml-encoders-for-serializer
 * @link https://github.com/symfony/symfony/blob/3.2/src/Symfony/Component/Serializer/Encoder/CsvEncoder.php
 */
class CsvEncoder implements EncoderInterface
{
    const FORMAT = 'csv';

    private $delimiter;
    private $enclosure;
    private $escapeChar;
    private $keySeparator;

    /**
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escapeChar
     * @param string $keySeparator
     */
    public function __construct($delimiter = ',', $enclosure = '"', $escapeChar = '\\', $keySeparator = '.')
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escapeChar = $escapeChar;
        $this->keySeparator = $keySeparator;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When a line in data has an incorrect structure (eg wrong column number)
     */
    public function encode($data, $format, array $context = array())
    {
        $handle = fopen('php://temp,', 'w+');

        if (!is_array($data)) {
            $data = array(array($data));
        } elseif (empty($data)) {
            $data = array(array());
        } else {
            // Sequential arrays of arrays are considered as collections
            $i = 0;
            foreach ($data as $key => $value) {
                if ($i !== $key || !is_array($value)) {
                    $data = array($data);
                    break;
                }

                ++$i;
            }
        }

        $headers = null;
        foreach ($data as $value) {
            $result = array();
            $this->flatten($value, $result);

            if (null === $headers) {
                $headers = array_keys($result);
                fputcsv($handle, $headers, $this->delimiter, $this->enclosure, $this->escapeChar);
            } elseif (array_keys($result) !== $headers) {
                throw new \InvalidArgumentException('To use the CSV encoder, each line in the data array must have the same structure. You may want to use a custom normalizer class to normalize the data format before passing it to the CSV encoder.');
            }

            fputcsv($handle, $result, $this->delimiter, $this->enclosure, $this->escapeChar);
        }

        rewind($handle);
        $value = stream_get_contents($handle);
        fclose($handle);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format)
    {
        return self::FORMAT === $format;
    }

    /**
     * Flattens an array and generates keys including the path.
     *
     * @param array  $array
     * @param array  $result
     * @param string $parentKey
     */
    private function flatten(array $array, array &$result, $parentKey = '')
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->flatten($value, $result, $parentKey.$key.$this->keySeparator);
            } else {
                $result[$parentKey.$key] = $value;
            }
        }
    }
}
