<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package;

use Proximum\Vimeet\Application\Command\Package\Step\SelectPlan;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlansType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Sheet $sheet */
        $sheet = $options['sheet'];

        $builder
            ->add('plan', ChoiceType::class, [
                'expanded'    => true,
                'multiple'    => false,
                'label'       => false,
                'choice_name' => 'id',
                'choices'     => $sheet->getPackage()->getAvailablePlans(),
                'required'    => true,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(['sheet']);
        $optionsResolver->addAllowedTypes('sheet', Sheet::class);
        $optionsResolver->setDefaults([
            'data_class' => SelectPlan::class,
        ]);
    }
}
