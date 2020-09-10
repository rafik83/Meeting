<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Sheet;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExternalShowUploadedFileAction
{
    /** @var string */
    private $sharedUploadedFiles;

    public function __construct(
        string $sharedUploadedFiles
    ) {
        $this->sharedUploadedFiles = $sharedUploadedFiles;
    }

    public function __invoke(string $path): BinaryFileResponse
    {
        $fullPath = sprintf('%s/%s', $this->sharedUploadedFiles, $path);

        return new BinaryFileResponse($fullPath);
    }
}
