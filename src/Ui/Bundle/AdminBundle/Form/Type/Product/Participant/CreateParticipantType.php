<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Participant;

use Proximum\Vimeet\Application\Command\Product\Participant\CreateParticipant;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\AbstractCreateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateParticipantType extends AbstractCreateType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationsType::class,
                'label'      => false,
            ])
            ->add('quantityMax', IntegerType::class, [
                'required' => false,
                'attr'     => [
                    'min' => 0,
                ],
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
            'data_class' => CreateParticipant::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_create_participant';
    }
}
