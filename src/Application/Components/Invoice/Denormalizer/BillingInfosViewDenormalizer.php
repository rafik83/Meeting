<?php

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use Proximum\Vimeet\Application\View\Invoice\BillingInfosView;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BillingInfosViewDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        // If the billingInfosViewOfSheet is not given in context, recreate it from the invoice info
        if (!isset($context['billingInfosViewOfSheet'])
            || !$context['billingInfosViewOfSheet'] instanceof BillingInfosView
        ) {
            $bilingInfosView = new BillingInfosView(
                isset($data['gender']) ? $data['gender'] : null,
                isset($data['lastname']) ? $data['lastname'] : null,
                isset($data['firstname']) ? $data['firstname'] : null,
                isset($data['function']) ? $data['function'] : null,
                isset($data['phone']) ? $data['phone'] : null,
                isset($data['mobile']) ? $data['mobile'] : null,
                isset($data['email']) ? $data['email'] : null,
                isset($data['company']) ? $data['company'] : null,
                isset($data['street']) ? $data['street'] : null,
                isset($data['zipcode']) ? $data['zipcode'] : null,
                isset($data['city']) ? $data['city'] : null,
                isset($data['country']) ? $data['country'] : null,
                isset($data['vatNumber']) ? $data['vatNumber'] : null,
                isset($data['reference']) ? $data['reference'] : null
            );
        } else {
            /** @var BillingInfosView $bilingInfosView */
            $bilingInfosView = $context['billingInfosViewOfSheet'];
            $bilingInfosView->country   = $data['country'];
            $bilingInfosView->vatNumber = isset($data['vatNumber']) ? $data['vatNumber'] : null;
        }

        return $bilingInfosView;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return BillingInfosView::class === $type && isset($data['country']);
    }
}
