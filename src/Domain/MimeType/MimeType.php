<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\MimeType;

final class MimeType
{
    public const FORMAT_IMAGE = 'image';
    public const FORMAT_PDF = 'pdf';
    public const FORMAT_PPT = 'ppt';
    public const FORMAT_CSV = 'csv';

    public const AVAILABLE_MIME_TYPES_BY_FORMAT = [
        self::FORMAT_IMAGE => self::IMAGE_MIME_TYPES,
        self::FORMAT_PDF => self::PDF_MIME_TYPES,
        self::FORMAT_PPT => self::PPT_MIME_TYPES,
        self::FORMAT_CSV => self::CSV_MIME_TYPES,
    ];

    public const IMAGE_MIME_TYPES = [
        'image/gif',
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/x-png',
    ];

    public const IMAGE_EXTENSIONS = [
        'jpeg',
        'jpg',
        'png',
        'gif',
    ];

    public const PDF_MIME_TYPES = [
        'application/pdf',
    ];

    public const PPT_MIME_TYPES = [
        'application/vnd.ms-powerpoint', // ppt
        'application/vnd.openxmlformats-officedocument.presentationml.presentation', // pptx
    ];

    public const CSV_MIME_TYPES = [
        'text/csv',
    ];

    public static function getMimeTypesByFormats(array $formats = []): array
    {
        $mimeTypes = [];
        $availableMimeTypesByFormat = self::AVAILABLE_MIME_TYPES_BY_FORMAT;

        foreach ($formats as $format) {
            if (!\array_key_exists($format, $availableMimeTypesByFormat)) {
                continue;
            }

            foreach ($availableMimeTypesByFormat[$format] as $availableMimeType) {
                $mimeTypes[] = $availableMimeType;
            }
        }

        return $mimeTypes;
    }
}
