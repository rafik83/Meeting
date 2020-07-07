<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening;

use Proximum\Vimeet\Application\Command\Happening\AbstractHappeningCommand;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class HappeningType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $event = $options['event'];

        $builder
            ->add('category', CategoryType::class, ['event' => $event, 'locale' => $options['locale']])
            ->add('happeningType', ChoiceType::class, [
                'choices' => [
                    'form.happening.happeningType.default.label' => AbstractHappeningCommand::TYPE_DEFAULT,
                    'form.happening.happeningType.webinar.label' => AbstractHappeningCommand::TYPE_WEBINAR,
                    'form.happening.happeningType.interactive_webinar.label' => AbstractHappeningCommand::TYPE_WEBINAR_INTERACTIVE,
                ],
                'expanded' => true,
                'required' => true,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationType::class,
                'label' => false,
            ])
            ->add('begin', DateTimePickerType::class, [
                'format' => 'd/m/Y H:i',
                'view_timezone' => $event->getTimeZone(),
                'attr'  => [
                    'class' => 'datetimepicker-range-element',
                ],
            ])
            ->add('end', DateTimePickerType::class, [
                'format' => 'd/m/Y H:i',
                'view_timezone' => $event->getTimeZone(),
                'attr'  => [
                    'class' => 'datetimepicker-range-element',
                ],
            ])
            ->add('questionAllowed', ChoiceType::class, [
                'choices'  => [
                    'form.happening_create.children.questionAllowed.answer.true'  => true,
                    'form.happening_create.children.questionAllowed.answer.false' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('limitParticipant', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'min' => 0,
                ],
                'help' => 'form.happening_create.children.limitParticipant.help',
            ])
            ->add('types', TypeChoiceType::class, [
                'event' => $options['event'],
                'expanded' => true,
                'locale' => $options['locale'],
                'multiple' => true,
                'required' => false,
                'user' => $options['admin'],
            ])
            ->add('talkings', CollectionType::class, [
                'required' => false,
                'entry_type' => TalkingType::class,
                'entry_options' => ['label' => false, 'event' => $event],
                'prototype_data' => ['speaker' => null, 'position' => 0],
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('invitationCode', TextType::class, [
                'required' => false,
            ])
            ->add('liveUrl', UrlType::class, [
                'required' => false,
                'help' => 'form.happening_create.children.liveUrl.help',
                'default_protocol' => 'https',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($options) {
            /** @var Admin $admin */
            $admin = $options['admin'];
            $form = $event->getForm();

            if ($admin->isSuperAdmin()) {
                $form
                    ->add('webinarRecorded', CheckboxType::class, [
                        'required' => false,
                    ])
                ;
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        foreach ($view->children['translations'] as $translation) {
            $translation->vars['label'] = Intl::getLocaleBundle()->getLocaleName($translation->vars['name']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['event', 'locale', 'admin']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('admin', Admin::class);
        $resolver->setAllowedTypes('locale', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'happening';
    }
}
