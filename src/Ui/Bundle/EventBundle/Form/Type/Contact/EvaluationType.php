<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Contact;

use Proximum\Vimeet\Application\Command\Contact\EditEvaluation;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\RatingType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('evaluation', RatingType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => EditEvaluation::class,
            ]
        );
    }
}
