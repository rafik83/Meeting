<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Happening;

use Doctrine\ORM\EntityRepository;
use Proximum\Vimeet\Application\Command\Happening\Participate;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\ParticipantEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipateHappeningType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('participants', ParticipantEntityType::class, [
                'sheet'         => $options['sheet'],
                'locale'        => $options['locale'],
                'multiple'      => true,
                'expanded'      => true,
                'query_builder' => function (EntityRepository $entityRepository) use ($options) {
                    return $entityRepository
                        ->createQueryBuilder('participant')
                        ->where('participant.sheet = :sheet')
                        ->setParameter('sheet', $options['sheet'])
                        ->andWhere('NOT EXISTS (SELECT ph.id FROM Entity:HappeningParticipation ph WHERE ph.happening = :happening AND ph.participant = participant)')
                        ->setParameter('happening', $options['happening']);
                },
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'sheet',
            'happening',
            'locale',
        ]);

        $resolver->setDefaults([
            'data_class'    => Participate::class,
            'csrf_token_id' => 'participate_happening',
        ]);
    }
}
