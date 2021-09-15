<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\RegistrationPath;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Type\RegistrationPath\View\AnswerView;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Event $event */
        $event = $options['event'];

        $builder
            ->add(
                'translatedTitle',
                TranslationsType::class,
                [
                    'label' => false,
                    'locales' => $event->getLocales(),
                    'entry_type' => TextareaType::class,
                    'entry_options' => [
                        'attr' => [
                            'class' => 'tinymce',
                        ],
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setDefaults(
            [
                'data_class' => AnswerView::class,
            ]
        );
    }
}
