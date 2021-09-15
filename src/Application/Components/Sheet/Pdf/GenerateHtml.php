<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Pdf;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfosHelper;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TaggedDataFactory;

/**
 * This class generates the html of the sheet print
 */
class GenerateHtml
{
    const PDF_TEMPLATE   = 'AdminBundle:Sheet/Pdf:index.html.twig';
    const SHEET_TEMPLATE = 'AdminBundle:Sheet/Pdf:sheet.html.twig';

    /** @var TaggedDataFactory */
    private $taggedDataFactory;

    /** @var SheetInfosHelper */
    private $sheetInfosHelper;

    /** @var RouterInterface */
    private $router;

    /** @var TemplatingAdapterInterface */
    private $templating;

    /**
     * @param RouterInterface            $router
     * @param TemplatingAdapterInterface $templating
     * @param TaggedDataFactory          $taggedDataFactory
     * @param SheetInfosHelper           $sheetInfosHelper
     */
    public function __construct(
        RouterInterface $router,
        TemplatingAdapterInterface $templating,
        TaggedDataFactory $taggedDataFactory,
        SheetInfosHelper $sheetInfosHelper
    ) {
        $this->router            = $router;
        $this->templating        = $templating;
        $this->taggedDataFactory = $taggedDataFactory;
        $this->sheetInfosHelper  = $sheetInfosHelper;
    }

    /**
     * @param string $scheme
     * @param string $host
     */
    public function setContext(string $scheme, string $host)
    {
        $context = $this->router->getContext();

        $context->setHost($host);
        $context->setScheme($scheme);
    }

    /**
     * @param Event   $event
     * @param Sheet[] $sheets
     * @param string  $locale
     *
     * @return string
     */
    public function printSheets(Event $event, array $sheets, string $locale): string
    {
        $print = '';

        foreach ($sheets as $sheet) {
            $print .= $this->generateSheetHtml($sheet, $event, $locale);
        }

        $html = $this->templating->render(self::PDF_TEMPLATE, [
            'event'  => $event,
            'print'  => $print,
            'locale' => $locale,
        ]);

        return $html;
    }

    /**
     * @param Sheet  $sheet
     * @param Event  $event
     * @param string $locale
     *
     * @throws \DomainException
     *
     * @return string
     */
    private function generateSheetHtml(Sheet $sheet, Event $event, string $locale): string
    {
        // Build sheet template data and attach tagged data view to template object with tags
        $templateData = $this->taggedDataFactory->buildTaggedDataViewForPrint($sheet, $locale);
        $users        = $sheet->getUsers();
        $user         = reset($users);

        if (false === $user) {
            throw new \DomainException('A sheet can not have 0 user');
        }

        list($nomenclatures, $participants, $taggedData) = $this->sheetInfosHelper->getInfos(
            $sheet,
            $user,
            $locale
        );

        $template = $this->templating->render(self::SHEET_TEMPLATE, [
            'event'         => $event,
            'sheet'         => $sheet,
            'taggedData'    => $taggedData,
            'locale'        => $locale,
            'nomenclatures' => $nomenclatures,
            'participants'  => $participants,
            'templateData'  => $templateData,
        ]);

        return $template;
    }
}
