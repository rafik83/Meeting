<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Expression;

class DataRangeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($options['first_name'], $options['entry_type'], $options['first_options'])
            ->add($options['second_name'], $options['entry_type'], $options['second_options'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['entry_type']);
        $resolver->setDefaults([
            'first_name'     => 'first',
            'second_name'    => 'second',
            'first_options'  => [],
            'second_options' => [],
            'operator'       => '<',
            'message'        => 'Invalid value',
            'error_bubbling' => false,
            'constraints'    => function (Options $options) {
                return [
                    new Expression([
                        'expression' => sprintf(
                            "value['%s'] %s value['%s']",
                            $options['first_name'],
                            $options['operator'],
                            $options['second_name']
                        ),
                        'message'    => $options['message'],
                    ]),
                ];
            },
        ]);
        $resolver->setAllowedValues('operator', ['==', '===', '>', '>=', '<', '<=']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'data_range';
    }
}
