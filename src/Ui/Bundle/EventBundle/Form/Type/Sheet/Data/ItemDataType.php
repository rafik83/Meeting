<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label'       => false,
                'required'    => false,
                'placeholder' => $options['placeholder'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'collection']);
        $resolver->setDefaults([
            'placeholder' => null,
            'data_class'  => Item::class,
            'empty_data'  => function (Options $options) {
                return function (FormInterface $form) use ($options) {
                    return new Item($options['collection'], $form->get('title')->getData());
                };
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $data = $view->vars['data'];

        if ($data instanceof Item && $data->getCollection()->getLocale() !== $data->getCollection()->getFallback()) {
            $view->vars['form']->children['title']->vars['help'] = $data->getFallbackTitle();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_item_data';
    }
}
