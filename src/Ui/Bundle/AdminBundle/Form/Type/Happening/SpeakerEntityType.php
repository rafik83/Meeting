<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening;

use Doctrine\ORM\EntityRepository;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpeakerEntityType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setDefaults(
            [
                'class' => Speaker::class,
                'query_builder' => function (Options $options) {
                    return function (EntityRepository $entityRepository) use ($options) {
                        return $entityRepository
                            ->createQueryBuilder('speaker')
                            ->where('speaker.event = :event')
                            ->setParameter('event', $options['event'])
                            ->orderBy('speaker.lastname', 'asc')
                            ->addOrderBy('speaker.firstname', 'asc');
                    };
                },
                'choice_label' => function (Speaker $speaker) {
                    if ($speaker->getUser() === null) {
                        return $speaker->getName();
                    }

                    return $this->translator->trans('form.happening_create_update.speaker_linked_to_user', [
                        '%name%' => $speaker->getName()
                    ], 'forms');

                },
            ]
        );
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
