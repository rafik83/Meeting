<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Common;

use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Model\Email;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType as CoreEmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', CoreEmailType::class, [
                'label'       => false,
                'placeholder' => 'form.email.placeholder',
                'required'    => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Email::class,
        ]);
    }
}
