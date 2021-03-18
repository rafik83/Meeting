<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Form;

use Proximum\Vimeet\Application\Command\Template\Form\Create;
use Proximum\Vimeet\Application\Command\Template\Form\UpdateParameters;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateParametersType extends AbstractFormTemplateType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('published', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'data_class' => UpdateParameters::class,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'form_template_update_parameters';
    }
}
