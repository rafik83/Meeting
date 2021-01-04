<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package;

use Proximum\Vimeet\Application\Command\Package\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\Model\OptionsType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\Model\ParticipantAndPlanningType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\Model\PlansType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('plans', PlansType::class, [
                'event'  => $options['event'],
                'locale' => $options['locale'],
                'label'  => false,
            ])
            ->add('participantAndPlanning', ParticipantAndPlanningType::class, [
                'event'  => $options['event'],
                'locale' => $options['locale'],
                'label'  => false,
            ])
            ->add('options', OptionsType::class, [
                'event'  => $options['event'],
                'locale' => $options['locale'],
                'label'  => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => Update::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'package_update';
    }
}
