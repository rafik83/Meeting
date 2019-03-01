<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type;

use Proximum\Vimeet\Application\Command\Type\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\PackageChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\FormTemplateChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\RegistrationTemplateChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\SheetTemplateChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeUpdateType extends AbstractType
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (false === $this->sheetRepository->isThereAtLeastOneByType($options['type'])) {
            $builder
                ->add('sheetTemplate', SheetTemplateChoiceType::class, [
                    'event'       => $options['event'],
                    'required'    => true,
                    'expanded'    => false,
                    'multiple'    => false,
                ])
                ->add('registrationTemplate', RegistrationTemplateChoiceType::class, [
                    'event'       => $options['event'],
                    'required'    => true,
                    'expanded'    => false,
                    'multiple'    => false,
                ])
                ->add('package', PackageChoiceType::class, [
                    'currentEvent' => $options['event'],
                    'required'     => true,
                    'expanded'     => false,
                    'multiple'     => false,
                ])
            ;
        }

        $builder
            ->add('translations', CollectionType::class, [
                'entry_type' => TypeTranslationType::class,
                'label'      => false,
            ])
            ->add('rank', IntegerType::class, [
                'required' => false,
            ])
            ->add('hidden', CheckboxType::class, [
                'required' => false,
            ])
            ->add('numberOfMeetingsPerPlanning', IntegerType::class, [
                'required' => false,
                'help' => 'form.type_update.children.numberOfMeetingsPerPlanning.help',
            ])
            ->add('validationCriteria', TypeValidationCriteriaType::class, [
                'required' => false,
            ])
            ->add('formTemplates', FormTemplateChoiceType::class, [
                'event' => $options['event'],
                'required' => false,
                'multiple' => true,
                'attr'     => [
                    'class' => 'select2',
                ],
            ])
            ->add('enableUnavailabilityManagement', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'    => Update::class,
            'csrf_token_id' => 'type_update',
        ]);

        $resolver->setRequired(['event', 'type']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('type', Type::class);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['translations'] as $translation) {
            $translation->vars['label'] = ucfirst(Intl::getLocaleBundle()->getLocaleName(
                $translation->vars['name'])
            );
        }
    }
}
