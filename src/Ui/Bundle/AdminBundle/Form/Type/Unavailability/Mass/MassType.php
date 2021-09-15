<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Mass;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DataRangeType;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Category\ChoiceType as CategoryChoiceType;
use Symfony\Component\Form\AbstractType as BaseAbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as BaseChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class MassType extends BaseAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Event $event */
        $event = $options['event'];

        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('category', CategoryChoiceType::class, [
                'event'    => $options['event'],
                'required' => true,
            ])
            ->add('types', TypeChoiceType::class, [
                'event' => $options['event'],
                'user' => $options['user'],
                'locale' => $options['locale'],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('begin', DateTimePickerType::class, [
                'format'        => 'd/m/Y H:i',
                'view_timezone' => $event->getTimeZone(),
            ])
            ->add('end', DateTimePickerType::class, [
                'format'        => 'd/m/Y H:i',
                'required'      => true,
                'view_timezone' => $event->getTimeZone(),
            ])
            ->add('blocking', BaseChoiceType::class, [
                'choices'  => [
                    'form.unavailability.mass.children.blocking.answer.true'  => true,
                    'form.unavailability.mass.children.blocking.answer.false' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationType::class,
                'label'      => false,
            ])
            ->add('dispatch', CheckboxType::class, [
                'required' => false,
            ])
            ->add('timeSlots', CollectionType::class, [
                'error_bubbling' => false,
                'allow_add'      => true,
                'allow_delete'   => true,
                'entry_type'     => DataRangeType::class,
                'entry_options'  => [
                    'label'          => false,
                    'entry_type'     => DateTimePickerType::class,
                    'first_name'     => 'from',
                    'second_name'    => 'to',
                    'first_options'  => ['view_timezone' => $options['event']->getTimeZone()],
                    'second_options' => ['view_timezone' => $options['event']->getTimeZone()],
                    'message'        => 'validators.unavailability.mass.children.time_slots.range_error',
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['translations'] as $translation) {
            $translation->vars['label'] = Intl::getLocaleBundle()->getLocaleName($translation->vars['name']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'user', 'locale']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('user', Admin::class);
        $resolver->setAllowedTypes('locale', 'string');
    }
}
