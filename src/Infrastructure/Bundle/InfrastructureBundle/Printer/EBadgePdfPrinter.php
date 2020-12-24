<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer;

use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class EBadgePdfPrinter
{
    /** @var RouterInterface */
    private $router;

    /** @var PdfPrinter */
    private $pdfPrinter;

    public function __construct(
        RouterInterface $router,
        PdfPrinter $pdfPrinter
    ) {
        $this->router = $router;
        $this->pdfPrinter = $pdfPrinter;
    }

    public function generate(UserEventToken $userEventToken): string
    {
        $pathToPdf = sprintf(
            '%s/edbadge-%s.pdf',
            sys_get_temp_dir(),
            $userEventToken->getToken()
        );

        $urlToPrint = $this->router->generate(
            'event_sheet_user_badge_download',
            [
                'token' => $userEventToken->getToken(),
                'format' => 'html',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->pdfPrinter->generate($urlToPrint, $pathToPdf, PdfPrinter::RENDER_TYPE_BADGE);
    }
}
