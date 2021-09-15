<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject\ButtonLink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ButtonLinkDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', UrlType::class, ['placeholder' => $options['placeholder']])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'object']);
        $resolver->setDefaults([
            'data_class'  => ButtonLink::class,
            'placeholder' => null,
            'help'        => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_button_link_data';
    }
}
