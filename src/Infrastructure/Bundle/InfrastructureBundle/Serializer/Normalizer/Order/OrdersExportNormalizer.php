<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Order;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Order\Export\OrdersExportView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OrdersExportNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private $charset = Charset::UTF_8;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof OrdersExportView) {
            throw new \Exception('Invalid object');
        }

        if (isset($context['charset']) && $context['charset'] !== $this->charset) {
            $this->charset = $context['charset'];
        }

        $columns = $this->createColumnName($object);
        $data[]  = $columns;

        foreach ($object->orders as $order) {
            $order->setColumnArray($columns);
            $data[] = $this->normalizer->normalize($order, $format, $context);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof OrdersExportView && 'csv' === $format;
    }

    /**
     * @param OrdersExportView $object
     *
     * @return array
     */
    private function createColumnName(OrdersExportView $object)
    {
        $data = [];

        // Shared columns
        foreach ($object->sharedColumnsTranslationView->getAllColumns() as $columnKey => $columnTranslation) {
            $data[$columnKey] = $this->convertCharset($columnTranslation);
        }

        foreach ($object->promotionCodes as $promotionCode) {
            $data[$promotionCode->getQuantityColumnId()] = $this->convertCharset($promotionCode->promotionCodeTitleWithQuantityTranslation);
            $data[$promotionCode->getTotalColumnId()]    = $this->convertCharset($promotionCode->promotionCodeTitleWithTotalTranslation);
        }

        foreach ($object->customRowsColumns as $customRow) {
            $data[$customRow->getTitleColumnId()] = $this->convertCharset($customRow->title);
            $data[$customRow->getUnitPriceColumnId()] = $this->convertCharset($customRow->unitPriceTitle);
            $data[$customRow->getQuantityColumnId()] = $this->convertCharset($customRow->quantityTitle);
            $data[$customRow->getTotalColumnId()] = $this->convertCharset($customRow->totalTitle);
        }

        // product column
        foreach ($object->products as $product) {
            $data[$product->getUnitPriceColumnId()] = $this->convertCharset($product->productTitleWithUnitPriceTranslation);
            $data[$product->getQuantityColumnId()] = $this->convertCharset($product->productTitleWithQuantityTranslation);
            $data[$product->getTotalColumnId()] = $this->convertCharset($product->productTitleWithTotalTranslation);
        }

        return $data;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    private function convertCharset($input)
    {
        if (Charset::UTF_8 !== $this->charset) {
            return iconv(Charset::UTF_8, Charset::WINDOWS_1252 . '//TRANSLIT//IGNORE', $input);
        }

        return $input;
    }
}
