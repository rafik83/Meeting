<?php

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\View\Order\Export\PromotionCodeView;

class PromotionCodeViewQueryHandler
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param PromotionCodeViewQuery $query
     *
     * @return PromotionCodeView
     */
    public function handle(PromotionCodeViewQuery $query)
    {
        $promotionCodeTitle = $query->promotionCode->getTitle();

        return new PromotionCodeView(
            $query->promotionCode->getId(),
            $promotionCodeTitle,
            $this->translator->trans('order.column.promotionCode.quantity', ['%promotionCodeTitle%' => $promotionCodeTitle], 'export', $query->adminLocale),
            $this->translator->trans('order.column.promotionCode.total', ['%promotionCodeTitle%' => $promotionCodeTitle], 'export', $query->adminLocale)
        );
    }
}
