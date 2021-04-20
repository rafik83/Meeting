<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type\Badge;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Type\Badge\Configure;
use Proximum\Vimeet\Application\Command\Type\Badge\MirroringAndFullHeightImageIncompatibilityException;
use Proximum\Vimeet\Application\Command\Type\Badge\NoLeftImageToRemoveException;
use Proximum\Vimeet\Application\Command\Type\Badge\NoRightImageToRemoveException;
use Proximum\Vimeet\Application\Command\Type\Badge\NoRightImageToSetFullHeightException;
use Proximum\Vimeet\Application\Command\Type\Badge\RemovingWhileAddingLeftImageException;
use Proximum\Vimeet\Application\Command\Type\Badge\RemovingWhileAddingRightImageException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\BadgeRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Badge\ConfigureType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ConfigureAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var Environment */
    private $twig;

    /** @var RouterInterface */
    private $router;

    /** @var BadgeRepositoryInterface */
    private $badgeRepository;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        BadgeRepositoryInterface $badgeRepository,
        CommandBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        Environment $twig,
        RouterInterface $router,
        TranslatorInterface $translator
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->badgeRepository = $badgeRepository;
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->flashBag = $flashBag;
        $this->twig = $twig;
        $this->router = $router;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Event $event, Type $type): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $type->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $badge = $this->badgeRepository->findByType($type);

        $configure = new Configure($event, $type, $badge);
        $form = $this->formFactory->create(ConfigureType::class, $configure, [
            'type' => $type,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($configure);

                $this->flashBag->add('success', 'flash.admin.type.badge.configuration.success');

                return new RedirectResponse(
                    $this->router->generate(
                        'admin_type_badge_configuration',
                        [
                            'event' => $event->getId(),
                            'type' => $type->getId(),
                        ]
                    )
                );
            } catch (RemovingWhileAddingLeftImageException $exception) {
                $formError = new FormError(
                    $this->translator->trans('validators.badge.removingWhileAddingLeftImage', [], 'validators')
                );
                $form->get('removeLeftImage')->addError($formError);
            } catch (RemovingWhileAddingRightImageException $exception) {
                $formError = new FormError(
                    $this->translator->trans('validators.badge.removingWhileAddingRightImage', [], 'validators')
                );
                $form->get('removeRightImage')->addError($formError);
            } catch (NoLeftImageToRemoveException $exception) {
                $formError = new FormError(
                    $this->translator->trans('validators.badge.noLeftImageToRemove', [], 'validators')
                );
                $form->get('removeLeftImage')->addError($formError);
            } catch (NoRightImageToRemoveException $exception) {
                $formError = new FormError(
                    $this->translator->trans('validators.badge.noRightImageToRemove', [], 'validators')
                );
                $form->get('removeRightImage')->addError($formError);
            } catch (NoRightImageToSetFullHeightException $exception) {
                $formError = new FormError(
                    $this->translator->trans('validators.badge.noRightImageToSetFullHeight', [], 'validators')
                );
                $form->get('isRightImageFullHeight')->addError($formError);
            } catch (MirroringAndFullHeightImageIncompatibilityException $exception) {
                $formError = new FormError(
                    $this->translator->trans('validators.badge.mirroringAndFullHeightImageIncompatibilityt', [], 'validators')
                );
                $form->get('isMirrored')->addError($formError);
            }
        }

        return new Response($this->twig->render('AdminBundle:Type/Badge:configure.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'badge' => $badge,
            'type' => $type,
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]));
    }
}
