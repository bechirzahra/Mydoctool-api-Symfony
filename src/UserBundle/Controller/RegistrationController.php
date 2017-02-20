<?php

namespace UserBundle\Controller;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;

use UserBundle\Entity\Invite;
use UserBundle\Entity\User;
use UserBundle\Entity\DoctorPatient;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class RegistrationController extends FOSRestController
{
    /**
     * @Route("/register", name="rest_registration")
     * @Method({"POST"})
     */
    public function registerAction(Request $request)
    {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');
        $em = $this->getDoctrine()->getEntityManager();

        $inviteSlug = $request->request->get('invite_slug');

        if (isset($inviteSlug) && !empty($inviteSlug)) {
            $invite = $em->getRepository('UserBundle:Invite')->findOneBySlug($inviteSlug);
            if (!$invite) {
                throw new NotFoundHttpException('This Invite does not exists.');
            }
            if ($invite->getType() == Invite::REGISTER_USER) {
                $user = $invite->getUser();
                $user->setEnabled(true);
            } else {
                $user = $userManager->createUser();
                $user->setEnabled(true);
                $event = new GetResponseUserEvent($user, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

                if (null !== $event->getResponse()) {
                    return $event->getResponse();
                }
            }
        }

        $form = $formFactory->createForm();
        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isValid()) {

            // let's check if their is an invite
            if (isset($inviteSlug) && !empty($inviteSlug)) {
                $invite->setAccepted(true);

                if ($invite->isDoctorType()) {
                    if ($invite->getType() === Invite::REGISTER_JOIN_ORGANIZATION_MANAGER) {
                        $user->addRole('ROLE_MANAGER');
                    }
                    $user->addRole('ROLE_DOCTOR');
                    $user->setType(User::USER_DOCTOR);
                    $user->setOrganization($invite->getFromOrganization());

                } else {
                    $user->setType(User::USER_PATIENT);
                }
            } else {
                $user->setType(User::USER_DOCTOR);
            }

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

            $userManager->updateUser($user);
            $em->flush();

            /** @var JWTManager $jwtManager */
            $jwtManager = $this->get('lexik_jwt_authentication.jwt_manager');

            $jwt = $jwtManager->create($user);
            $response = new JsonResponse();
            $event = new AuthenticationSuccessEvent(array('token' => $jwt), $user, $request, $response);
            // $event->setResponse($response);
            $dispatcher->dispatch(Events::AUTHENTICATION_SUCCESS, $event);
            $response->setData($event->getData());

            return $response;
        } else {
            $view = $this->view($form)
                ->setFormat('json');
        }

        return $this->handleView($view);
    }

    /**
     * Tell the user to check his email provider
     */
    public function checkEmailAction()
    {
        $email = $this->get('session')->get('fos_user_send_confirmation_email/email');
        $this->get('session')->remove('fos_user_send_confirmation_email/email');
        $user = $this->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->render('FOSUserBundle:Registration:checkEmail.html.twig', array(
            'user' => $user,
        ));
    }

    /**
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction(Request $request, $token)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

        $userManager->updateUser($user);

        if (null === $response = $event->getResponse()) {
            $url = $this->generateUrl('fos_user_registration_confirmed');
            $response = new RedirectResponse($url);
        }

        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));

        return $response;
    }

    /**
     * Tell the user his account is now confirmed
     */
    public function confirmedAction()
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('FOSUserBundle:Registration:confirmed.html.twig', array(
            'user' => $user,
            'targetUrl' => $this->getTargetUrlFromSession(),
        ));
    }

    private function getTargetUrlFromSession()
    {
        // Set the SecurityContext for Symfony <2.6
        if (interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
            $tokenStorage = $this->get('security.token_storage');
        } else {
            $tokenStorage = $this->get('security.context');
        }

        $key = sprintf('_security.%s.target_path', $tokenStorage->getToken()->getProviderKey());

        if ($this->get('session')->has($key)) {
            return $this->get('session')->get($key);
        }
    }
}
