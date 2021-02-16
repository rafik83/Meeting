<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\Accommodation;

use Proximum\Vimeet\Application\Command\Rooming\Accommodation\Update;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractAccommodationType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'data_class' => Update::class
            ])
        ;
    }
}
