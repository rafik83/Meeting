<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class IndexType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('submit', SubmitType::class, [
                'confirm' => 'admin.elasticsearch.index.confirm',
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'elasticsearch_index';
    }
}
