<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\Event;

use Proximum\Vimeet\Application\ThirdParty\LENI\User\Import\Import;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\CharsetChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, ['required' => true])
            ->add('charset', CharsetChoiceType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Import::class]);
    }
}
