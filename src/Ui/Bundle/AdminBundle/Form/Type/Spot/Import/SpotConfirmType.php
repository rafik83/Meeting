<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot\Import;

use Proximum\Vimeet\Application\Command\Spot\Import\SpotImportConfirm;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpotConfirmType extends AbstractType
{
    /** @var TranslatorAdapter */
    private $translator;

    /**
     * @param TranslatorAdapter $translator
     */
    public function __construct(TranslatorAdapter $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('submit', SubmitType::class, [
            'confirm' => $this->translator->trans(
                'form.spot.import.confirm',
                [],
                'forms',
                $options['locale']
            ),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('locale');
        $resolver->setDefaults(['data_class' => SpotImportConfirm::class]);
    }
}
