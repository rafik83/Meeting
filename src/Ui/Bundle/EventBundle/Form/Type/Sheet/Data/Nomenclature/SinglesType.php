<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\Nomenclature;

use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Nomenclature\ItemToSinglesMultipleTransformer;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Nomenclature\ItemToSinglesTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SinglesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $nomenclature = $options['nomenclature'];
        $constraints = $options['constraints'];

        if (!$nomenclature instanceof Nomenclature) {
            throw new \Exception(sprintf('"%s" expected, "%s" given.', Nomenclature::class, \gettype($nomenclature)));
        }

        if (false === $options['multiple'] || $nomenclature->getDepth() > 1) {
            $builder->addModelTransformer(new ItemToSinglesTransformer($nomenclature));
        } else {
            $builder->addModelTransformer(new ItemToSinglesMultipleTransformer($nomenclature));
        }

        if (1 === $nomenclature->getDepth()) {
            $builder
                ->add('first', SingleType::class, [
                    'choices'      => $nomenclature->getFirstLevel(),
                    'locale'       => $options['locale'],
                    'label'        => false,
                    'placeholder'  => $options['placeholder'],
                    'nomenclature' => $nomenclature,
                    'multiple'     => $options['multiple'],
                    'constraints'  => $constraints,
                ])
            ;
        } elseif (2 === $nomenclature->getDepth()) {
            $builder
                ->add('first', SingleType::class, [
                    'choices'      => $nomenclature->getFirstLevel(),
                    'locale'       => $options['locale'],
                    'label'        => false,
                    'nomenclature' => $nomenclature,
                    'constraints'  => $constraints,
                ])
                ->add('second', SingleType::class, [
                    'choices'      => $nomenclature->getSecondLevel(),
                    'locale'       => $options['locale'],
                    'label'        => false,
                    'placeholder'  => $options['placeholder'],
                    'nomenclature' => $nomenclature,
                    'choice_attr'  => function (NomenclatureItem $item) {
                        return $item->getParent() ? ['data-parent' => $item->getParent()->getKey()] : [];
                    },
                    'constraints'  => $constraints,
                ])
            ;
        } elseif (3 === $nomenclature->getDepth()) {
            $builder
                ->add('first', SingleType::class, [
                    'choices'      => $nomenclature->getFirstLevel(),
                    'locale'       => $options['locale'],
                    'label'        => false,
                    'nomenclature' => $nomenclature,
                    'constraints'  => $constraints,
                ])
                ->add('second', SingleType::class, [
                    'choices'      => $nomenclature->getSecondLevel(),
                    'locale'       => $options['locale'],
                    'label'        => false,
                    'nomenclature' => $nomenclature,
                    'choice_attr' => function (NomenclatureItem $item) {
                        return $item->getParent() ? ['data-parent' => $item->getParent()->getKey()] : [];
                    },
                    'constraints'  => $constraints,
                ])
                ->add('third', SingleType::class, [
                    'choices'      => $nomenclature->getThirdLevel(),
                    'locale'       => $options['locale'],
                    'label'        => false,
                    'placeholder'  => $options['placeholder'],
                    'nomenclature' => $nomenclature,
                    'choice_attr'  => function (NomenclatureItem $item) {
                        return $item->getParent() ? ['data-parent' => $item->getParent()->getKey()] : [];
                    },
                    'constraints'  => $constraints,
                ])
            ;
        } else {
            throw new \Exception(sprintf('Not implementation for depth of %s', $nomenclature->getDepth()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'nomenclature', 'placeholder']);
        $resolver->setDefaults([
            'multiple' => false,
            'constraints' => [],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        // Put data-parent="" on select inputs
        $keys = array_keys($view->children);
        foreach ($keys as $index => $parent) {
            if (isset($keys[$index + 1])) {
                $child = $keys[$index + 1];
                $view->children[$child]->vars['attr']['data-parent'] = $view->children[$parent]->vars['id'];
            }
        }
    }
}
