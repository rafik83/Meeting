<?php

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\View\Invoice\CustomRowView;
use Proximum\Vimeet\Application\View\Invoice\GroupView;
use Proximum\Vimeet\Application\View\Invoice\RowView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class GroupViewDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $rowViews = [];

        foreach ($data['products'] as $product) {
            $rowView = $this->denormalizer->denormalize($product, RowView::class, $format, $context);

            if (null !== $rowView) {
                $rowViews[] = $rowView;
            }
        }

        $customRowViews = [];

        foreach ($data['customRows'] as $customRow) {
            $customRowViews[] = $this->denormalizer->denormalize($customRow, CustomRowView::class, $format, $context);
        }

        if (empty($rowViews) && empty($customRowViews)) {
            return null;
        }

        return new GroupView(
            $data['label'],
            $data['type'],
            $data['groupId'],
            $rowViews,
            $customRowViews
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return GroupView::class === $type
            && isset($data['label'])
            && isset($data['type'])
            && isset($data['products'])
            && is_array($data['products'])
            && isset($data['customRows'])
            && is_array($data['customRows']);
    }
}
