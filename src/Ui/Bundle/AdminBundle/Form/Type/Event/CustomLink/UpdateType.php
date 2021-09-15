<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\CustomLink;

use Proximum\Vimeet\Application\Command\Event\CustomLink\Update;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractCustomLinkType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            [
                'data_class' => Update::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'custom_link_update';
    }
}
