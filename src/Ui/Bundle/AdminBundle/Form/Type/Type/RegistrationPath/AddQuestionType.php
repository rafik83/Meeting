<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\RegistrationPath;

use Proximum\Vimeet\Domain\Type\RegistrationPath\View\AddQuestion;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Content\ContentTranslationType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'translatedTitle',
                TranslationsType::class,
                [
                    'label' => false,
                    'locales' => $options['event']->getLocales(),
                    'entry_type' => TextareaType::class,
                ]
            )
            //->add('answers', CollectionType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setDefaults(
            [
                'data_class' => AddQuestion::class,
            ]
        );
    }
}
