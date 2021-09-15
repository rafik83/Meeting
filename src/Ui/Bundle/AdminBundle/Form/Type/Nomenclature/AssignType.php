<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature;

use Proximum\Vimeet\Application\Command\Nomenclature\Assign;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\AdminEventChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssignType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('event', AdminEventChoiceType::class, ['admin' => $options['admin'], 'help' => 'form.nomenclature_assign_type.children.event.help'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['admin']);
        $resolver->setAllowedTypes('admin', Admin::class);
        $resolver->setDefaults([
            'data_class' => Assign::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'nomenclature_assign_type';
    }
}
