<?php

namespace Proximum\Vimeet\Application\Components\Order\Normalizer;

use Proximum\Vimeet\Application\View\Order\RowView;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RowViewNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param RowView $object
     * @param string  $format
     * @param array   $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'label' => $object->label,
            'quantity' => $object->quantity,
            'price' => AmountFormatter::decimalToCentsAmount($object->price),
            'total' => AmountFormatter::decimalToCentsAmount($object->total),
            'productId' => $object->productId,
            'vatRate' => $object->vatRate,
            'customRows' => $this->normalizer->normalize($object->customRows, $format, $context),
            'includedProducts' => $this->normalizer->normalize($object->includedProducts, $format, $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof RowView;
    }
}
