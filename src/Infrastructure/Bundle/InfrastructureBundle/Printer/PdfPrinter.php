<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer;

use Symfony\Component\Process\Process;

class PdfPrinter
{
    public const RENDER_TYPE_DEFAULT = 'default';
    public const RENDER_TYPE_BADGE = 'badge';

    /** @var string */
    private $phantomjsPath;

    /** @var string */
    private $phantomjsScript;

    /** @var string */
    private $phantomjsHttpUser;

    /** @var string */
    private $phantomjsHttpPassword;

    /** @var string */
    private $phantomjsRenderScriptPath;

    /** @var string */
    private $phantomjsRenderBadgeScript;

    public function __construct(
        string $phantomjsPath,
        string $phantomjsScript,
        string $phantomjsRenderScriptPath,
        string $phantomjsRenderBadgeScript,
        string $phantomjsHttpUser,
        string $phantomjsHttpPassword
    ) {
        $this->phantomjsPath = $phantomjsPath;
        $this->phantomjsScript = $phantomjsScript;
        $this->phantomjsRenderScriptPath = $phantomjsRenderScriptPath;
        $this->phantomjsRenderBadgeScript = $phantomjsRenderBadgeScript;
        $this->phantomjsHttpUser = $phantomjsHttpUser;
        $this->phantomjsHttpPassword = $phantomjsHttpPassword;
    }

    public function generate(string $urlToPrint, string $pathToPdf, string $renderType = self::RENDER_TYPE_DEFAULT): string
    {
        $process = new Process(
            [
                $this->phantomjsPath,
                $this->getRenderScript($renderType),
                $urlToPrint,
                $pathToPdf,
                $this->phantomjsHttpUser,
                $this->phantomjsHttpPassword,
            ],
            null,
            [
                'OPENSSL_CONF' => '/etc/ssl/'
            ]
        );

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $pathToPdf;
    }

    private function getRenderScript(string $renderType): string
    {
        if ($renderType === self::RENDER_TYPE_BADGE) {
            return sprintf('%s%s', $this->phantomjsRenderScriptPath, $this->phantomjsRenderBadgeScript);
        }

        return $this->phantomjsScript;
    }
}
