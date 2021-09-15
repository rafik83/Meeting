<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\SearchFacet;

use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TranslationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'form.search_facet_translation.children.label.label',
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if (SearchFacet::hasPlaceholder($data['type'])) {
                $form->add(
                    'placeholder',
                    TextareaType::class,
                    [
                        'label' => 'form.search_facet_translation.children.placeholder.label',
                    ]
                );
            }
        });
    }
}
