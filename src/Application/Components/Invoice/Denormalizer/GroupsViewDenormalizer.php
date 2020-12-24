<?php

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\View\Invoice\GroupsView;
use Proximum\Vimeet\Application\View\Invoice\GroupView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class GroupsViewDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $groupViews = [];

        foreach ($data['groups'] as $group) {
            $groupView = $this->denormalizer->denormalize($group, GroupView::class, $format, $context);

            if (null !== $groupView) {
                $groupViews[] = $this->denormalizer->denormalize($group, GroupView::class, $format, $context);
            }
        }

        return new GroupsView($groupViews);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return GroupsView::class === $type && isset($data['groups']) && is_array($data['groups']);
    }
}
