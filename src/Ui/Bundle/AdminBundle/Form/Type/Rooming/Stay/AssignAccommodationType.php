<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\Stay;

use function Clue\StreamFilter\fun;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AssignAccommodation;
use Proximum\Vimeet\Application\Query\Rooming\Accommodation\AccommodationListByPeriodQuery;
use Proximum\Vimeet\Application\Query\Rooming\Stay\GetSheetUsers;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Rooming\Stay\HasStayForPeriod;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignAccommodationType extends AbstractType
{
    /** @var QueryBusInterface */
    private $queryBus;
    /** @var HasStayForPeriod */
    private $hasStayForPeriod;

    public function __construct(
        QueryBusInterface $queryBus,
        HasStayForPeriod $hasStayForPeriod
    ) {
        $this->queryBus = $queryBus;
        $this->hasStayForPeriod = $hasStayForPeriod;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['assignAccommodation']->user;
        $event = $options['assignAccommodation']->event;
        $arrival = $options['assignAccommodation']->arrival;
        $departure = $options['assignAccommodation']->departure;

        $builder
            ->add('arrival', DateTimePickerType::class, [
                'display_hour' => false,
            ])
            ->add('departure', DateTimePickerType::class, [
                'display_hour' => false,
            ])
            ->add('accommodation', ChoiceType::class, [
                'choices' => $this->queryBus->handle(new AccommodationListByPeriodQuery($event, $arrival, $departure)),
                'choice_label' => function (Accommodation $accommodation) {
                    return $accommodation->getTitle();
                },
                'attr' => [
                    'class' => 'select2',
                ],
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
                'choices' => $this->queryBus->handle(new GetSheetUsers($user, $event)),
                'choice_label' => function (User $user) {
                    return $user->getFullname();
                },
                'attr' => [
                    'class' => 'select2',
                ],
                'choice_attr' => function(User $user) use ($event, $arrival, $departure) {
                    if ($this->hasStayForPeriod->isSatisfiedBy($event, $user, $arrival, $departure)) {
                        return [
                            'disabled' => 'disabled',
                            'class' => 'disabled',
                        ];
                    }

                    return [];
                },
                'placeholder' => 'form.admin_assign_accommodation_type.roommate.none',
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
