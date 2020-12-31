<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PracticalInfo;

use Proximum\Vimeet\Application\Command\Event\PracticalInfo\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organiserName', TextType::class, [
                'required' => true,
            ])
            ->add('contactLastName', TextType::class, [
                'required' => false,
            ])
            ->add('contactFirstName', TextType::class, [
                'required' => false,
            ])
            ->add('organiserPhone', TextType::class, [
                'required' => false,
            ])
            ->add('organiserEmail', EmailType::class, [
                'required' => false,
            ])
            ->add('organiserWebsite', UrlType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data-class' => Update::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'event_practical_info_update';
    }
}
