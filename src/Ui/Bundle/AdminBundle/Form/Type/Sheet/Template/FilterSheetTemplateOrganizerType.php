<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template;

use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\EventChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterSheetTemplateOrganizerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('event', EventChoiceType::class, [
                'required'         => false,
                'expanded'         => false,
                'multiple'         => false,
                'label'            => 'form.filter_sheet_template_organizer.children.event.label',
                'repositoryMethod' => function (EventRepositoryInterface $eventRepository) use ($options) {
                    return $eventRepository->getEventsByAdmin($options['admin']);
                },
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['admin'])
            ->setDefault('allow_extra_fields', true);
    }
}
