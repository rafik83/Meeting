<?php

namespace Proximum\Vimeet\Domain\Happening\Webinar;

use Proximum\Vimeet\Domain\Model\Happening;

class IsRecordedFileAccessible
{
    public function isSatisfiedBy(Happening $happening): bool
    {
        if (!$happening->isWebinarRecorded()) {
            return false;
        }

        return $happening->hasWebinarRecordZipFileUrl();
    }
}
