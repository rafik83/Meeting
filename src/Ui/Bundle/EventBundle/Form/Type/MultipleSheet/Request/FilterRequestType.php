<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\MultipleSheet\Request;

use Proximum\Vimeet\Application\Query\MultipleSheets\Request\FilterRequestView;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class FilterRequestType extends AbstractType
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var TranslatorInterface */
    private $translator;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param TranslatorInterface      $translator
     * @param UserRepositoryInterface  $userRepository
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        TranslatorInterface $translator,
        UserRepositoryInterface $userRepository
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->translator = $translator;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('otherSheet', ChoiceType::class, [
                'choices' => $this->sheetRepository->getSheetsMetBySheets($options['event'], $options['sheets']),
                'choice_label' => function (Sheet $sheet) {
                    return $sheet->getTitle();
                },
                'required' => false,
                'attr'     => [
                    'class'            => 'form-control select2',
                    'data-placeholder' => '',
                ],
            ])
            ->add('state', ChoiceType::class, [
                'required' => false,
                'choices' => array_merge([
                    Request::STATE_PLANNED,
                ], Request::getAllStates()),
                'choice_label' => function ($value) {
                    return 'form.multiple_sheet_request_filter_request_type.children.state.filter.' . $value;
                },
                'attr'     => [
                    'class'            => 'form-control select2',
                    'data-placeholder' => $this->translator->trans('form.multiple_sheet_request_filter_request_type.children.state.filter.all', [], 'forms'),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'required'     => false,
                'choices'      => [Request::TYPE_REQUEST, Request::TYPE_PROPOSITION],
                'choice_label' => function ($value) {
                    return 'form.multiple_sheet_request_filter_request_type.children.type.filter.' . $value;
                },
                'attr'     => [
                    'class'            => 'form-control select2',
                    'data-placeholder' => $this->translator->trans('form.multiple_sheet_request_filter_request_type.children.type.filter.all', [], 'forms'),
                ],
            ])
            ->add('user', ChoiceType::class, [
                'required' => false,
                'choices' => array_merge([
                    FilterRequestView::NO_PREFERENCE,
                ], $this->userRepository->getUsersParticipantOfSheets($options['sheets'])),
                'choice_label' => function ($user) {
                    if ($user instanceof User) {
                        return $user->getFullname();
                    }

                    return 'form.multiple_sheet_request_filter_request_type.children.user.noPreference.label';
                },
                'attr' => [
                    'class' => 'form-control select2',
                    'data-placeholder' => '',
                ],
            ])
        ;

        if (count($options['sheets']) > 1) {
            $builder
                ->add(
                    'sheetConcerned',
                    ChoiceType::class,
                    [
                        'required' => false,
                        'choices' => $options['sheets'],
                        'choice_label' => function (Sheet $sheet) {
                            return $sheet->getTitle();
                        },
                        'attr' => [
                            'class' => 'form-control select2',
                            'data-placeholder' => '',
                        ],
                    ]
                );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('data_class', FilterRequestView::class)
            ->setRequired(['event', 'sheets'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'multiple_sheet_request_filter_request_type';
    }
}
