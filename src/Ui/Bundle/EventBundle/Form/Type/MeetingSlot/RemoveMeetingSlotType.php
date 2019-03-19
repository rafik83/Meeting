<?php
/**
 * Created by PhpStorm.
 * User: taner
 * Date: 19/03/19
 * Time: 07:43
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\MeetingSlot;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\MeetingSlot\Remove;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RemoveMeetingSlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $options['locale'];

        $builder
            ->add('content', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => 300,
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired([
                'locale'
            ])
            ->setDefaults([
                'data_class' => Remove::class,
            ])
        ;
    }
}