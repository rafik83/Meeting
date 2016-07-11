<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum Vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Nomenclature;

final class Charset
{
    const UTF_8        = 'UTF-8';
    const ISO_8859_1   = 'ISO-8859-1';
    const WINDOWS_1252 = 'Windows-1252';

    /**
     * @param string      $inFilename
     * @param string      $inCharset
     * @param string      $outCharset
     * @param string|null $outFilename
     *
     * @return string
     */
    public static function convert($inFilename, $inCharset, $outCharset, $outFilename = null)
    {
        if ($inCharset !== $outCharset) {
            $input       = file_get_contents($inFilename);
            $outFilename = $outFilename ? : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'charset-' . uniqid();
            $output      = iconv($inCharset, $outCharset, $input);

            file_put_contents($outFilename, $output);

            return $outFilename;
        }

        return $inFilename;
    }
}
