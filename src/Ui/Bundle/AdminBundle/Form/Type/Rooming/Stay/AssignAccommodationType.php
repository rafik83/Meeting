<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\Stay;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AssignAccommodation;
use Proximum\Vimeet\Application\Query\Rooming\Accommodation\AccommodationListByPeriodQuery;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignAccommodationType extends AbstractType
{
    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(QueryBusInterface $queryBus)
    {
        $this->queryBus = $queryBus;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $event = $options['assignAccommodation']->event;
        $arrival = $options['assignAccommodation']->arrival;
        $departure = $options['assignAccommodation']->departure;

        $builder
            ->add('arrival', DateType::class)
            ->add('departure', DateType::class)
            ->add('accommodation', ChoiceType::class, [
                'choices' => $this->queryBus->handle(new AccommodationListByPeriodQuery($event, $arrival, $departure)),
                'choice_label' => function (Accommodation $accommodation) {
                    return $accommodation->getTitle();
                }
            ])
            ->add('roomType', ChoiceType::class, [
                'choices' => [
                    Stay::ROOM_TYPE_SINGLE,
                    Stay::ROOM_TYPE_DOUBLE,
                ],
                'choice_label' => function ($type) {
                    return sprintf('form.admin_assign_accommodation_type.roomType.%s', $type);
                },
                'expanded' => true,
            ])
            ->add('roommate', ChoiceType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('assignAccommodation')
            ->setAllowedTypes('assignAccommodation', AssignAccommodation::class)
            ->setDefaults([
                'data_class' => AssignAccommodation::class,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'admin_assign_accommodation_type';
    }
}
