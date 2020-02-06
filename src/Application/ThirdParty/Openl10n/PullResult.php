<?php

namespace Proximum\Vimeet\Application\ThirdParty\Openl10n;

class PullResult
{
    public $skippedFiles = [];

    public $downloadedFiles = [];

    public function addSkippedFiles(string $file): void
    {
        $this->skippedFiles[] = $file;
    }

    public function addDownloadedFiles(string $file): void
    {
        $this->downloadedFiles[] = $file;
    }
}
