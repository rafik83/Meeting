<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package;

use Proximum\Vimeet\Domain\Model;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackageChoiceType extends AbstractType
{
    /**
     * @var PackageRepositoryInterface
     */
    private $packageRepository;

    /**
     * @param PackageRepositoryInterface $packageRepository
     */
    public function __construct(PackageRepositoryInterface $packageRepository)
    {
        $this->packageRepository = $packageRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['currentEvent']);
        $resolver->setAllowedTypes('currentEvent', Model\Event::class);
        $resolver->setDefaults([
            'currentEvent'     => [],
            'choices'          => function (Options $options) {
                return $options['repositoryMethod']($this->packageRepository);
            },
            'choice_label'     => 'title',
            'repositoryMethod' => function (Options $options) {
                return function (PackageRepositoryInterface $packageRepository) use ($options) {
                    return $packageRepository->findByEvent($options['currentEvent']);
                };
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'package_choice_type';
    }
}
