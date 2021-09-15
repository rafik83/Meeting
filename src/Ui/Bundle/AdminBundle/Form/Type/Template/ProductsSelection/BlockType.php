<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\ProductsSelection;

use Proximum\Vimeet\Domain\Template\Block;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', CollectionType::class, [
                'entry_type' => TextType::class,
                'label'      => false,
                'required'   => false,
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['label'] as $translation) {
            $translation->vars['label'] = ucfirst(Intl::getLocaleBundle()->getLocaleName($translation->vars['name']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Block::class,
        ]);
    }
}
