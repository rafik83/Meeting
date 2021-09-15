<?php

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\View\Invoice\Vat\VatListView;
use Proximum\Vimeet\Application\View\Invoice\Vat\VatView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class VatListViewDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $vatViews = [];

        foreach ($data['vatViews'] as $vatViewData) {
            $vatViews[] = $this->denormalizer->denormalize($vatViewData, VatView::class, $format, $context);
        }

        return new VatListView(
            $data['total'],
            $data['totalWithVat'],
            $data['vatApplicable'],
            $data['vatMode'],
            $vatViews
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return VatListView::class === $type;
    }
}
