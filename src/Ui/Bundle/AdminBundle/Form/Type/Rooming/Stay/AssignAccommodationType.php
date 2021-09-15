<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\Stay;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AssignAccommodation;
use Proximum\Vimeet\Application\Query\Rooming\Accommodation\AccommodationListByPeriodQuery;
use Proximum\Vimeet\Application\Query\Rooming\Stay\GetRoommates;
use Proximum\Vimeet\Application\Query\Rooming\Stay\GetSheetsByEventQuery;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Rooming\Stay\HasStayForPeriod;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
        /** @var AssignAccommodation $assignAccommodation */
        $assignAccommodation = $options['assignAccommodation'];
        $event               = $assignAccommodation->event;
        $arrival             = $assignAccommodation->arrival;
        $departure           = $assignAccommodation->departure;
        $otherSheet          = $assignAccommodation->otherSheet;
        $user                = $assignAccommodation->user;

        $sheets = $otherSheet ? $this->queryBus->handle(new GetSheetsByEventQuery($event)) : [];

        $builder
            ->add('arrival', DateTimePickerType::class, [
                'display_hour' => false,
                'view_timezone' => $event->getTimeZone(),
                'format' => 'd/m/Y',
            ])
            ->add('departure', DateTimePickerType::class, [
                'display_hour' => false,
                'view_timezone' => $event->getTimeZone(),
                'format' => 'd/m/Y',
            ])
            ->add('accommodation', ChoiceType::class, [
                'choices' => $this->queryBus->handle(new AccommodationListByPeriodQuery($event, $arrival, $departure)),
                'choice_label' => function (Accommodation $accommodation) {
                    return $accommodation->getTitle();
                },
                'choice_value' => function (?Accommodation $accommodation = null) {
                    if ($accommodation === null) {
                        return null;
                    }
                    return $accommodation->getId();
                },
                'attr' => [
                    'class' => 'select2',
                ],
            ])
            ->add('roomNumber', TextType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('roomType', ChoiceType::class, [
                'choices' => [
                    Stay::ROOM_TYPE_SINGLE,
                    Stay::ROOM_TYPE_DOUBLE,
                    Stay::ROOM_TYPE_TWIN,
                ],
                'choice_label' => function ($type) {
                    return sprintf('form.admin_assign_accommodation_type.roomType.%s', $type);
                },
                'expanded' => true,
            ])
            ->add('displayOtherSheet', ButtonType::class)
            ->add('otherSheet', ChoiceType::class, [
                'required' => false,
                'choices' => $sheets,
                'choice_label' => function (Sheet $sheet) {
                    return $sheet->getTitle();
                },
                'choice_value' => function (?Sheet $sheet = null) {
                    if ($sheet === null) {
                        return null;
                    }
                    return $sheet->getId();
                },
                'attr' => [
                    'class' => 'form-control select2',
                ],
            ])
            ->add('roommate', ChoiceType::class, [
                'required' => false,
                'choices' => $this->queryBus->handle(new GetRoommates($user, $event, $otherSheet)),
                'choice_label' => function (User $user) {
                    return $user->getFullname();
                },
                'choice_value' => function (?User $user = null) {
                    if ($user === null) {
                        return null;
                    }
                    return $user->getId();
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
