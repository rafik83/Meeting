<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\ChangeType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\TypeView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeTypeType extends AbstractType
{
    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices'      => $this->typeRepository->getTypeViewsByEvent(
                    $options['event'],
                    $options['locale'],
                    $options['type']
                ),
                'required'     => true,
                'expanded'     => true,
                'choice_label' => 'title',
                'choice_value' => 'id',
            ]);

        $builder->get('type')->addModelTransformer(
            new CallbackTransformer(
                function (Type $type) {
                    return new TypeView($type->getId(), '', '', $type->isHidden());
                },
                function (TypeView $typeview = null) {
                    if (null === $typeview) {
                        return null;
                    }

                    return $this->typeRepository->getById($typeview->id);
                }
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'type', 'locale']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('type', Type::class);
        $resolver->setDefaults(['data_class' => ChangeType::class]);
    }
}
