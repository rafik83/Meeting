<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\Nomenclature\CheckboxesType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\Nomenclature\SinglesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class NomenclatureDataType extends AbstractType
{
    /**
     * @var NomenclatureRepositoryInterface
     */
    private $nomenclatureRepository;

    /**
     * NomenclatureDataType constructor.
     *
     * @param NomenclatureRepositoryInterface $nomenclatureRepository
     */
    public function __construct(NomenclatureRepositoryInterface $nomenclatureRepository)
    {
        $this->nomenclatureRepository = $nomenclatureRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['object'] instanceof TemplateObject\Nomenclature) {
            throw new \Exception('Nomenclature object expected.');
        }

        $nomenclature = $this->nomenclatureRepository->findById($options['object']->getNomenclatureId());

        if ($options['object']->isSingles()) {
            $this->addSingles($nomenclature, $builder, $options, $options['object']);
        } elseif ($options['object']->isRadios()) {
            $this->addRadios($nomenclature, $builder, $options, $options['object']);
        } elseif ($options['object']->isMultiple()) {
            if (true === $options['onMultipleUseSinglesInsteadOfCheckboxes']) {
                $this->addSingles($nomenclature, $builder, $options, $options['object']);
            } else {
                $this->addCheckboxes($nomenclature, $builder, $options, $options['object']);
            }
        } else {
            throw new \Exception('Not implemented yet.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'object', 'placeholder']);
        $resolver->setDefaults(
            [
                'onMultipleUseSinglesInsteadOfCheckboxes' => false,
                'data_class'                              => TemplateObject\Nomenclature::class,
                'placeholder'                             => null,
                'help'                                    => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'nomenclature_data';
    }

    /**
     * @param Nomenclature                $nomenclature
     * @param FormBuilderInterface        $form
     * @param array                       $options
     * @param TemplateObject\Nomenclature $object
     */
    private function addRadios(
        Nomenclature $nomenclature,
        FormBuilderInterface $form,
        array $options,
        TemplateObject\Nomenclature $object
    ): void {
        $constraints = $this->getConstraints($object, $options['data_class']);

        $form->add(
            'item',
            ChoiceType::class,
            [
                'choices'                   => $nomenclature->getLastLevel(),
                'label'                     => $object->getOption('label', $options['locale']),
                'expanded'                  => true,
                'multiple'                  => false,
                'required'                  => $object->getOption('required'),
                'choice_translation_domain' => false,
                'choice_label'              => function (NomenclatureItem $item) use ($options) {
                    return $item->getLabel($options['locale']);
                },
                'constraints' => $constraints,
            ]
        );
    }

    /**
     * @param Nomenclature                $nomenclature
     * @param FormBuilderInterface        $form
     * @param array                       $options
     * @param TemplateObject\Nomenclature $object
     */
    private function addCheckboxes(
        Nomenclature $nomenclature,
        FormBuilderInterface $form,
        array $options,
        TemplateObject\Nomenclature $object
    ): void {
        $constraints = $this->getConstraints($object, $options['data_class']);

        $form->add(
            'items',
            CheckboxesType::class,
            [
                'nomenclature' => $nomenclature,
                'locale'       => $options['locale'],
                'label'        => $object->getOption('label', $options['locale']),
                'required'     => $object->getOption('required'),
                'constraints'  => $constraints,
            ]
        );
    }

    /**
     * @param Nomenclature                $nomenclature
     * @param FormBuilderInterface        $form
     * @param array                       $options
     * @param TemplateObject\Nomenclature $object
     */
    private function addSingles(
        Nomenclature $nomenclature,
        FormBuilderInterface $form,
        array $options,
        TemplateObject\Nomenclature $object
    ): void {
        $key = $object->isMultiple() ? 'items' : 'item';

        $constraints = $this->getConstraints($object, $options['data_class']);

        $form->add(
            $key,
            SinglesType::class,
            [
                'nomenclature' => $nomenclature,
                'locale'       => $options['locale'],
                'placeholder'  => $options['placeholder'],
                'label'        => $object->getOption('label', $options['locale']),
                'required'     => $object->getOption('required'),
                'multiple'     => $object->isMultiple(),
                'constraints'  => $constraints,
            ]
        );
    }

    private function getConstraints(TemplateObject\Nomenclature $nomenclatureObject, ?string $dataClass)
    {
        $constraints = [];

        if (null === $dataClass && $nomenclatureObject->isRequired()) {
            $constraints[] = new NotBlank();
        }

        return $constraints;
    }
}
