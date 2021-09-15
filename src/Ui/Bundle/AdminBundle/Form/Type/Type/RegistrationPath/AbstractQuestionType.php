<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\RegistrationPath;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractQuestionType extends AbstractType
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
                    'locales' => $event->getLocales(),
                    'entry_type' => TextareaType::class,
                    'entry_options' => [
                        'attr' => [
                            'class' => 'tinymce',
                        ],
                    ],
                ]
            )
            ->add('answers', AnswerCollectionType::class, ['event' => $event]);
    }

    public function getBlockPrefix()
    {
        return 'registration_path_question';
    }
}
