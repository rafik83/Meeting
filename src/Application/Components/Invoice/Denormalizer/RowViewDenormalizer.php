<?php

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\View\Invoice\CustomRowView;
use Proximum\Vimeet\Application\View\Invoice\IncludedProductView;
use Proximum\Vimeet\Application\View\Invoice\RowView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RowViewDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (0 === $data['quantity']) {
            return null;
        }

        $customRowViews = [];

        foreach ($data['customRows'] as $customRow) {
            $customRowViews[] = $this->denormalizer->denormalize($customRow, CustomRowView::class, $format, $context);
        }

        $includedProductViews = [];

        foreach ($data['includedProducts'] as $includedProduct) {
            $includedProductViews[] = $this->denormalizer->denormalize(
                $includedProduct,
                IncludedProductView::class,
                $format,
                $context
            );
        }

        return new RowView(
            $data['label'],
            $data['quantity'],
            $data['price'],
            $data['total'],
            $data['productId'],
            $customRowViews,
            $includedProductViews
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return RowView::class === $type
            && isset($data['label'])
            && isset($data['quantity'])
            && isset($data['price'])
            && isset($data['total'])
            && isset($data['productId'])
            && isset($data['customRows'])
            && is_array($data['customRows'])
            && isset($data['includedProducts'])
            && is_array($data['includedProducts']);
    }
}
