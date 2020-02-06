<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\RegistrationPath;

use Proximum\Vimeet\Application\Command\Type\RegistrationPath\AddQuestion;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddQuestionType extends AbstractQuestionType
{
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
