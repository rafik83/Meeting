<?php

namespace Proximum\Vimeet\Application\Components\Order\Normalizer;

use Proximum\Vimeet\Application\View\Order\IncludedProductView;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IncludedProductViewNormalizer implements NormalizerInterface
{
    /**
     * @param IncludedProductView $object
     * @param string              $format
     * @param array               $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'label'    => $object->label,
            'quantity' => $object->quantity,
            'price'    => AmountFormatter::decimalToCentsAmount($object->price),
            'total'    => AmountFormatter::decimalToCentsAmount($object->total),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof IncludedProductView;
    }
}
