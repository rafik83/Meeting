<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer\Package;

use Proximum\Vimeet\Application\Serializer\Normalizer\AbstractNormalizer;
use Proximum\Vimeet\Application\View\Package\ParticipantProductView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ParticipantProductViewNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    /**
     * @param ParticipantProductView $object
     * @param null|string            $format
     * @param array                  $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!isset($context['locale'])) {
            throw new \InvalidArgumentException('Missing "locale" in context parameter');
        }

        return [
            'id' => $object->id,
            'title' => $object->title,
            'description' => $object->description,
            'unitPrice' => $object->unitPrice,
            'unitPriceFormatted' => $this->translator->trans(
                'package.product.unitPrice',
                [
                    '%price%' => $object->unitPrice,
                    '%currency%' => Intl::getCurrencyBundle()->getCurrencySymbol($object->currency, $context['locale']),
                    '%vatMode%' => $this->translator->trans('package.vatMode.' . $object->vatMode),
                ]
            ),
            'vatMode' => $object->vatMode,
            'currency' => $object->currency,
            'quantityIncluded' => $object->quantityIncluded,
            'quantityMax' => $object->isInfiniteQuantityMax() ? 'Infinity' : $object->quantityMax,
            'quantityMaxFormatted' =>  $this->translator->trans('package.product.quantityMaxInformation', ['%quantityMax%' => $object->quantityMax]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ParticipantProductView;
    }
}
