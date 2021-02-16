<?php

namespace Proximum\Vimeet\Application\Query\Tip\Event;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\View\Tip\Event\PreviewTipView;

class PreviewTipViewQueryHandler
{
    /** @var TranslatorInterface */
    private $translator;

    /**
     * PreviewTipViewQueryHandler constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param PreviewTipViewQuery $query
     *
     * @return PreviewTipView
     */
    public function handle(PreviewTipViewQuery $query)
    {
        return new PreviewTipView(
            $query->tip->getTranslationTitle($query->locale),
            $query->tip->getTranslationContent($query->locale),
            array_map(function ($pageTranslationKey) {
                return $this->translator->trans($pageTranslationKey);
            }, $query->pages)
        );
    }
}
