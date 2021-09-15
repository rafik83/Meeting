<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Schedule;

use Proximum\Vimeet\Application\Command\MeetingSlot\Generate;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenerateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recipes', CollectionType::class, [
                'entry_type'    => RecipeType::class,
                'entry_options' => [
                    'label' => false,
                    'event' => $options['event'],
                ],
                'allow_add'     => true,
                'allow_delete'  => true,
                'label'         => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('event');
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => Generate::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'generate_slot';
    }
}
