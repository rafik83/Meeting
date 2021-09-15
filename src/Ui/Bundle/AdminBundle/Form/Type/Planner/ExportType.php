<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Planner;

use Proximum\Vimeet\Application\Command\Planner\ExportJobCreator;
use Proximum\Vimeet\Domain\Planner\ExportSolutionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lockMeetingRequest', ChoiceType::class, [
                'choices'  => [
                    'form.planner_export.children.lockMeetingRequest.option.yes' => true,
                    'form.planner_export.children.lockMeetingRequest.option.no'  => false,
                ],
                'expanded' => true,
            ])
            ->add('solutionType', ChoiceType::class, [
                'choices'      => ExportSolutionType::getExportSolutionTypes(),
                'choice_label' => function ($choice) {
                    return 'form.planner_export.children.solutionType.option.' . $choice;
                },
                'required'     => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExportJobCreator::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'planner_export';
    }
}
