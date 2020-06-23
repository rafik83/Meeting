<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant;

use Proximum\Vimeet\Application\Command\Participant\ImportMapping;
use Proximum\Vimeet\Application\View\Participant\ImportMappingView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportMappingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ImportMappingView $importMappingView */
        $importMappingView = $options['importMappingView'];

        $builder
            ->add('mappings', MappingType::class, [
                'csvHeaders' => $importMappingView->fieldHeaders,
                'registrationHeaders' => $importMappingView->registrationHeaders,
                'label' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['locale', 'importMappingView']);
        $resolver->setAllowedTypes('importMappingView', ImportMappingView::class);
        $resolver->setDefaults(['data_class' => ImportMapping::class]);
    }
}
