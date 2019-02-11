<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Catalog;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Product\Export\ProductsListView;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductsExportNormalizer implements NormalizerInterface
{
    public const TRANSLATION_DOMAIN  = 'export';

    public const COL_PRODUCT_NAME = 'productName';
    public const COL_UNIT_PRICE = 'unitPrice';
    public const COL_QUANTITY_UNIT = 'quantityUnit';
    public const COL_QUANTITY_PLAN = 'quantityPlan';
    public const COL_QUANTITY_TOTAL = 'quantityTotal';
    public const COL_PROMOTION = 'promotion';
    public const COL_SALES = 'sales';

    private $charset = Charset::UTF_8;

    /** @var TranslatorAdapter */
    private $translator;

    /**
     * @param TranslatorAdapter $translator
     */
    public function __construct(TranslatorAdapter $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        if (!$object instanceof ProductsListView) {
            throw new \LogicException('Invalid object');
        }

        if (isset($context['charset']) && $context['charset'] !== $this->charset) {
            $this->charset = $context['charset'];
        }

        $data = [];
        $locale = $object->locale;

        foreach ($object->products as $product) {
            $data = [
                $this->colTrans(self::COL_PRODUCT_NAME, $locale) => $this->convertCharset($product->name),
                $this->colTrans(self::COL_UNIT_PRICE, $locale) => $product->unitPrice,
                $this->colTrans(self::COL_QUANTITY_UNIT, $locale) => $product->quantityUnit,
                $this->colTrans(self::COL_QUANTITY_PLAN, $locale) => $product->quantityPlan,
                $this->colTrans(self::COL_QUANTITY_TOTAL, $locale) => $product->quantityTotal,
                $this->colTrans(self::COL_PROMOTION, $locale) => $product->promotion,
                $this->colTrans(self::COL_SALES, $locale) => $product->sales
            ];
        }

        return $data;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    private function convertCharset($input): string
    {
        if (Charset::UTF_8 !== $this->charset) {
            return iconv(Charset::UTF_8, Charset::WINDOWS_1252 . '//TRANSLIT', $input);
        }

        return $input;
    }

    /**
     * @param string $colName
     * @param string $locale
     *
     * @return string
     */
    private function colTrans(string $colName, string $locale): string
    {
        return $this->convertCharset(
            $this->translator->trans($colName, [], self::TRANSLATION_DOMAIN, $locale)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductsListView && 'csv' === $format;
    }
}
