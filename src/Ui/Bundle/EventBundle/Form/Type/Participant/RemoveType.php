<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant;

use Proximum\Vimeet\Application\Command\Participant\Remove;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RemoveType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $participants = $options['participants'];

        $builder
            ->add('participants', ChoiceType::class, [
                'expanded'    => true,
                'multiple'    => true,
                'label'       => false,
                'choice_name' => 'id',
                'choices'     => $participants,
                'required'    => true,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['participants']);
        $resolver->setDefaults([
            'data_class' => Remove::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'remove_participant';
    }
}
