<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VatModeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'form.vatMode.et'  => Event::VAT_MODE_ET,
                'form.vatMode.ati' => Event::VAT_MODE_ATI,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
