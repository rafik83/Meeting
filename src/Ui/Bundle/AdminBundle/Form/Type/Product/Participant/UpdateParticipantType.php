<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Participant;

use Proximum\Vimeet\Application\Command\Product\Participant\UpdateParticipant;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\AbstractUpdateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateParticipantType extends AbstractUpdateType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('quantityMax', IntegerType::class, [
                'required' => false,
                'attr'     => [
                    'min' => 0,
                ],
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationsType::class,
                'label'      => false,
            ])
        ;

        if (!empty($options['availabilityTimeRanges'])) {
            $builder
                ->add('availabilityTimeRanges', AvailabilityTimeRangeChoiceType::class, [
                    'choices' => $options['availabilityTimeRanges'],
                    'event' => $options['event'],
                    'locale' => $options['locale'],
                    'multiple' => true,
                    'required' => false,
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired([
            'event',
            'locale',
            'availabilityTimeRanges',
        ]);
        $resolver->setDefaults([
            'data_class' => UpdateParticipant::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_update_participant';
    }
}
