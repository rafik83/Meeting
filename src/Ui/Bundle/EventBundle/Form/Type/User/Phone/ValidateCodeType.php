<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\Phone;

use Proximum\Vimeet\Application\Command\User\Phone\ValidateCode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValidateCodeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'required' => true,
                'placeholder' => '____',
                'attr' => [
                    'style'     => 'letter-spacing: 40px; font-size: 60px;',
                    'maxLength' => 4,
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => ValidateCode::class]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_event_phone_validate_code';
    }
}
