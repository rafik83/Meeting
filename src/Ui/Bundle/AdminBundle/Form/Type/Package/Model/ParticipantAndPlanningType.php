<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\Model;

use Proximum\Vimeet\Application\Command\Package\Model\ParticipantAndPlanning;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\ProductChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantAndPlanningType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('labels', TranslationsType::class, [
                'entry_type' => TextType::class,
                'locales'    => $options['event']->getLocales(),
                'required'   => false,
            ])
            ->add('maxParticipant', IntegerType::class, [
                'attr'     => [
                    'min'  => 1,
                ],
                'help'     => 'form.package_update.children.participantAndPlanning.children.maxParticipant.help',
                'required' => false,
            ])
            ->add('participants', ProductCollectionType::class, [
                'event'            => $options['event'],
                'locale'           => $options['locale'],
                'product_types'    => [Product::TYPE_PARTICIPANT],
                'collection_group' => 'participants',
                'error_bubbling'   => false,
                'required'         => true,
            ])
            ->add('planning', ProductChoiceType::class, [
                'event'            => $options['event'],
                'locale'           => $options['locale'],
                'repositoryMethod' => function (ProductRepositoryInterface $productRepository) use ($options) {
                    return $productRepository->findByEventAndTypes($options['event'], [Product::TYPE_PLANNING]);
                },
                'required' => false,
            ])
            ->add('planningSelectable', CheckboxType::class, [
                'required' => false,
            ])
            ->add('participantWithPlanning', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => ParticipantAndPlanning::class,
        ]);
    }
}
