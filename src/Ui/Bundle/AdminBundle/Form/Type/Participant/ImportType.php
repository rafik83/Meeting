<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant;

use Proximum\Vimeet\Application\Command\Participant\Import;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\CharsetChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', TypeChoiceType::class, [
                'event' => $options['event'],
                'locale' => $options['locale'],
                'user' => $options['user'],
                'required' => true,
                'placeholder' => 'form.participant_import.children.type.placeholder',
            ])
            ->add('file', FileType::class, [
                'required' => true,
            ])
            ->add('charset', CharsetChoiceType::class)
            ->add('allowMultiSheet', ChoiceType::class, [
                'expanded' => true,
                'choices' => [
                    'form.participant_import.children.allowMultiSheet.choice.yes' => true,
                    'form.participant_import.children.allowMultiSheet.choice.no' => false
                ],
                'required' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['event', 'locale', 'user']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('user', Admin::class);

        $resolver->setDefaults([
            'data_class' => Import::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'participant_import';
    }
}
