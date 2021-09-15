<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Group\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Group\SheetChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sheet', SheetChoiceType::class, [
                'attr'        => [
                    'class'               => 'form-control select2',
                    'data-disallow-clear' => 'true',
                    'data-placeholder'    => '',
                ],
                'group'       => $options['group'],
                'required'    => true,
                'placeholder' => '',
            ])
            ->add('title', TextType::class, [
                'required' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['group']);
        $resolver->setAllowedTypes('group', Group::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_group_create_sheet';
    }
}
