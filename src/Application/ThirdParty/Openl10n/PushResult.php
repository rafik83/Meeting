<?php

namespace Proximum\Vimeet\Application\ThirdParty\Openl10n;

class PushResult
{
    public $uploadedFiles = [];

    public $createdFiles = [];

    public $addedLocales = [];

    public $unknownLocales = [];

    public function addUploadedFile(string $file): void
    {
        $this->uploadedFiles[] = $file;
    }

    public function addCreatedFile(string $file): void
    {
        $this->createdFiles[] = $file;
    }

    public function addLocale(string $locale): void
    {
        $this->addedLocales[] = $locale;
    }

    public function addUnknownLocales(string $locale): void
    {
        $this->unknownLocales[] = $locale;
    }
}
