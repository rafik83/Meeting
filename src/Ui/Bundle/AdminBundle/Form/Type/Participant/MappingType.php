<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MappingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['csvHeaders'] as $key => $csvHeader) {
            $builder->add($key, ChoiceType::class, [
                'label'   => $csvHeader,
                'choices' => array_flip($options['registrationHeaders']),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['csvHeaders', 'registrationHeaders']);
    }
}
