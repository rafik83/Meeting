<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['placeholder' => $options['titlePlaceholder']])
            ->add('url', UrlType::class, ['placeholder' => $options['linkPlaceholder']])
            ->add('type', ChoiceType::class, [
                'expanded' => true,
                'choices'  => [
                    'form.sheet_media_data.children.type.document' => 'document',
                    'form.sheet_media_data.children.type.video'    => 'video',
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['collection']);
        $resolver->setDefaults([
            'titlePlaceholder' => null,
            'linkPlaceholder' => 'https://',
            'data_class'  => Media::class,
            'empty_data'  => function (Options $options) {
                return function (FormInterface $form) use ($options) {
                    return new Media(
                        $options['collection'],
                        $form->get('title')->getData(),
                        $form->get('url')->getData(),
                        $form->get('type')->getData()
                    );
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

        if ($data instanceof Media && $data->getCollection()->getLocale() !== $data->getCollection()->getFallback()) {
            $view->vars['form']->children['title']->vars['help'] = $data->getFallbackTitle();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_media_data';
    }
}
