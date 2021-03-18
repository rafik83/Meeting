<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template;

use Proximum\Vimeet\Application\Command\Sheet\Template\UpdatePreview;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreviewType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('previewObjects', CollectionType::class, [
                'entry_type'    => ObjectChoiceType::class,
                'entry_options' => [
                    'templateObjects' => $options['templateObjects'],
                    'locale'          => $options['locale'],
                ],
                'allow_add'     => true,
                'allow_delete'  => true,
            ]);

        $builder->get('previewObjects')
            ->addModelTransformer(new ObjectDataTransformer($options['templateData']));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['templateObjects', 'templateData', 'locale']);
        $resolver->setDefaults([
            'data_class' => UpdatePreview::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_template_preview';
    }
}
