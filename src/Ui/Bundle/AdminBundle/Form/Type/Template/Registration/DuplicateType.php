<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Registration;

use Proximum\Vimeet\Application\Command\Template\Registration\Duplicate;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\EventChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DuplicateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('event', EventChoiceType::class, [
                'required'         => true,
                'expanded'         => false,
                'multiple'         => false,
                'repositoryMethod' => function (EventRepositoryInterface $eventRepository) use ($options) {
                    return $eventRepository->getEventsByAdmin($options['admin']);
                },
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['admin']);
        $resolver->setDefaults([
            'data_class' => Duplicate::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'registration_template_duplicate';
    }
}
