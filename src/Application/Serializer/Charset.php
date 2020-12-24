<?php

namespace Proximum\Vimeet\Application\Serializer;

use Proximum\Vimeet\Application\Nomenclature\Import\Exception\BadCharsetException;

final class Charset
{
    const UTF_8 = 'UTF-8';
    const ISO_8859_1 = 'ISO-8859-1';
    const WINDOWS_1252 = 'Windows-1252';

    /**
     * @param string      $inFilename
     * @param string      $inCharset
     * @param string      $outCharset
     * @param string|null $outFilename
     *
     * @throws BadCharsetException
     *
     * @return null|string
     */
    public static function convertFile($inFilename, $inCharset, $outCharset, $outFilename = null)
    {
        try {
            $input = file_get_contents($inFilename);
            $outFilename = $outFilename ?: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'charset-' . uniqid();
            $output = iconv($inCharset, $outCharset . '//TRANSLIT//IGNORE', $input);

            file_put_contents($outFilename, $output);
        } catch (\Exception $exception) {
            throw new BadCharsetException($exception->getMessage());
        }

        return $outFilename;
    }

    /**
     * @deprecated use convertFile()
     *
     * @throws BadCharsetException
     */
    public static function convert($inFilename, $inCharset, $outCharset, $outFilename = null)
    {
        return self::convertFile($inFilename, $inCharset, $outCharset, $outFilename);
    }

    /**
     * @param mixed|string $input
     * @param string       $fromCharset
     * @param string       $toCharset
     *
     * @return mixed|string
     */
    public static function convertString($input, $fromCharset = self::UTF_8, $toCharset = self::WINDOWS_1252)
    {
        if (!$input || !\is_string($input)) {
            return $input;
        }

        return iconv($fromCharset, $toCharset . '//TRANSLIT//IGNORE', $input);
    }
}
