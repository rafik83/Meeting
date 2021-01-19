<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Form;

use Proximum\Vimeet\Application\Command\Template\Form\Create;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractFormTemplateType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'data_class' => Create::class,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'form_template_create';
    }
}
