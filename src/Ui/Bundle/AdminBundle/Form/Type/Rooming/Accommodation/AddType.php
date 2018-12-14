<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\Accommodation;

use Proximum\Vimeet\Application\Command\Rooming\Accommodation\Add;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddType extends AbstractAccommodationType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'data_class' => Add::class
            ])
        ;
    }
}
