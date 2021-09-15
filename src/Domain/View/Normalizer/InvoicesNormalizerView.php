<?php

namespace Proximum\Vimeet\Domain\View\Normalizer;

use Proximum\Vimeet\Domain\View\Invoice\ExportView;

class InvoicesNormalizerView
{
    /**
     * @var ExportView[]
     */
    public $exportViews;

    /**
     * @var string
     */
    public $locale;

    /**
     * InvoicesNormalizerView constructor.
     *
     * @param ExportView[] $exportViews
     * @param string       $locale
     */
    public function __construct(array $exportViews, $locale)
    {
        $this->exportViews = $exportViews;
        $this->locale      = $locale;
    }
}
