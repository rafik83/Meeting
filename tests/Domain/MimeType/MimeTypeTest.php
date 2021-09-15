<?php

namespace Proximum\Vimeet\Tests\Domain\Messaging;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\MimeType\MimeType;

class MimeTypeTest extends TestCase
{
    public function testGetMimeTypesByFormats(): void
    {
        $imageMimeTypes = MimeType::getMimeTypesByFormats(['image']);
        $csvMimeTypes = MimeType::getMimeTypesByFormats(['csv']);
        $pdfMimeTypes = MimeType::getMimeTypesByFormats(['pdf']);
        $pptMimeTypes = MimeType::getMimeTypesByFormats(['ppt']);
        $imageAndPdfMimeTypes = MimeType::getMimeTypesByFormats(['image', 'pdf', 'ppt']);
        $emptyFormatMimeTypes = MimeType::getMimeTypesByFormats();
        $badFormatMimeTypes = MimeType::getMimeTypesByFormats(['bad-format']);

        $this->assertSame($imageMimeTypes, MimeType::IMAGE_MIME_TYPES);
        $this->assertSame($csvMimeTypes, MimeType::CSV_MIME_TYPES);
        $this->assertSame($pdfMimeTypes, MimeType::PDF_MIME_TYPES);
        $this->assertSame($pptMimeTypes, MimeType::PPT_MIME_TYPES);
        $this->assertSame(
            $imageAndPdfMimeTypes,
            array_merge(
                MimeType::IMAGE_MIME_TYPES,
                MimeType::PDF_MIME_TYPES,
                MimeType::PPT_MIME_TYPES
            )
        );
        $this->assertEmpty($emptyFormatMimeTypes);
        $this->assertEmpty($badFormatMimeTypes);
    }
}
