<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\RegistrationPath;

use Proximum\Vimeet\Application\Command\Type\RegistrationPath\AssignParticipationType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignParticipationTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'types',
            TypeChoiceType::class,
            [
                'event' => $options['event'],
                'locale' => $options['locale'],
                'user' => $options['admin'],
                'orderByTitle' => true,
                'exceptHidden' => true,
                'expanded' => true,
                'multiple' => true,
                'required' => true,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale', 'admin']);
        $resolver->setDefaults(
            [
                'data_class' => AssignParticipationType::class,
            ]
        );
    }
}
