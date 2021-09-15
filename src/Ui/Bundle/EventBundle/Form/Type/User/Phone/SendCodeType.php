<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\Phone;

use Proximum\Vimeet\Application\Command\User\Phone\SendCode;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Library\TelephoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SendCodeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('phone', TelephoneType::class, [
                'required' => true,
                'country' => $options['country'],
            ])
            ->add('accepted', CheckboxType::class, [
                'required' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['country']);
        $resolver->setDefaults(['data_class' => SendCode::class]);
    }
}
