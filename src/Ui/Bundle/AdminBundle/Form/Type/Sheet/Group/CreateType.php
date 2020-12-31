<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Group;

use Proximum\Vimeet\Application\Command\Sheet\Group\Create;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractGroupType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(['data_class' => Create::class]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'create_sheets_group';
    }
}
