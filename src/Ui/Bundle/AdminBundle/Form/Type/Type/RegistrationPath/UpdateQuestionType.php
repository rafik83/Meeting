<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\RegistrationPath;

use Proximum\Vimeet\Application\Command\Type\RegistrationPath\UpdateQuestion;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateQuestionType extends AbstractQuestionType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setDefaults(
            [
                'data_class' => UpdateQuestion::class,
            ]
        );
    }
}
