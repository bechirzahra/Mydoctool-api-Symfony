<?php

namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\Exception;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\GetResponseUserEvent;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use UserBundle\Entity\Invite;
use UserBundle\Entity\User;
use UserBundle\Entity\DoctorPatient;

use UserBundle\Form\Type\InviteFormType;

/**
 * @RouteResource("Invite")
 */
class InvitesController extends FOSRestController
{
    //[OPTIONS]
    public function optionsAction()
    {}

    // [GET]
    public function cgetAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $repository = $em->getRepository('UserBundle:Invite');

        $invites = $repository->findAll();
        $data = array('items' => $invites);

        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    public function getAction($inviteSlug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $invite = $em->getRepository('UserBundle:Invite')->findOneBySlug($inviteSlug);
        if (!$invite) {
            throw new NotFoundHttpException('Invite not found.');
        }

        $view = $this->view($invite);

        return $this->handleView($view);
    }

    public function postAction(Request $request)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');
        $em = $this->getDoctrine()->getEntityManager();

        $userEmail = htmlspecialchars($request->request->get('email'));
        $userFirstname = htmlspecialchars($request->request->get('firstname'));
        $userLastname = htmlspecialchars($request->request->get('lastname'));
        $type = $request->request->get('type');
        $view = null;

        if (isset($userEmail) && !empty($userEmail) && isset($type)) {
            // When creating the Organization, we should invite the manager to join it.
            $currentUser = $this->get('security.token_storage')->getToken()->getUser();

            // Has the Manager already an account?
            $existingUser = $em->getRepository('UserBundle:User')->findOneByEmail($userEmail);

            $invite = new Invite($userEmail);

            // In cas we invite a Doctor
            if ($type === 'doctor' || $type === 'manager') {
                $organizationSlug = $request->request->get('organization_slug');
                if (isset($organizationSlug) && !empty($organizationSlug)) {
                    $organization = $em->getRepository('UserBundle:Organization')->findOneBySlug($organizationSlug);
                } else {
                    $organization = $currentUser->getOrganization();
                }

                $moreData = array(
                    'firstname' => $userFirstname,
                    'lastname' => $userLastname
                );
                $invite->setMoreData($moreData);

                if (!$existingUser) {

                    if ($type === 'doctor') {
                        $invite->setType(Invite::REGISTER_JOIN_ORGANIZATION_DOCTOR);
                    } else {
                        $invite->setType(Invite::REGISTER_JOIN_ORGANIZATION_MANAGER);
                    }

                } else {
                    // If the user already has an organization, error.
                    if ($existingUser->getOrganization() !== null) {
                        $view = $this->view(array(), 409);
                        return $this->handleView($view);
                    }

                    if ($type === 'doctor') {
                        $invite->setType(Invite::JOIN_ORGANIZATION_DOCTOR);
                    } else {
                        $invite->setType(Invite::JOIN_ORGANIZATION_MANAGER);
                    }
                }

                $invite->setFromOrganization($organization);

            } else if ($type === 'patient') {

                if (!$existingUser) {

                    $existingUser = $userManager->createUser();
                    $event = new GetResponseUserEvent($existingUser, $request);
                    $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

                    if (null !== $event->getResponse()) {
                        return $event->getResponse();
                    }

                    $persistAndSave = true;
                    $invite->setType(Invite::REGISTER_USER);
                    $invite->setFromUser($currentUser);
                    $invite->setUser($existingUser);
                    $moreData = array(
                        'firstname' => $userFirstname,
                        'lastname' => $userLastname
                    );
                    $invite->setMoreData($moreData);
                    $existingUser->setEmail($userEmail);
                    $existingUser->setFirstname($userFirstname);
                    $existingUser->setLastname($userLastname);
                    $existingUser->setMaidenname(htmlspecialchars($request->request->get('maidenName')));
                    $existingUser->setBirthdayDay = htmlspecialchars($request->request->get('birthdayDay'));
                    $existingUser->setBirthdayMonth = htmlspecialchars($request->request->get('birthdayMonth'));
                    $existingUser->setBirthdayYear = htmlspecialchars($request->request->get('birthdayYear'));
                    $existingUser->setGender = htmlspecialchars($request->request->get('gender'));
                    $existingUser->setIpp = htmlspecialchars($request->request->get('ipp'));
                    $existingUser->setPhoneNumber = htmlspecialchars($request->request->get('phoneNumber'));
                    $existingUser->setInterventionInfo = htmlspecialchars($request->request->get('interventionInfo'));
                    $existingUser->setInterventionDay = htmlspecialchars($request->request->get('interventionDay'));
                    $existingUser->setInterventionMonth = htmlspecialchars($request->request->get('interventionMonth'));
                    $existingUser->setInterventionYear = htmlspecialchars($request->request->get('interventionYear'));
                    $existingUser->setOtherInfo = htmlspecialchars($request->request->get('otherInfo'));
                    $existingUser->setPlainPassword(" ");
                    $existingUser->setType(User::USER_PATIENT);
                    $userManager->updateUser($existingUser);
                    $doctorPatient = new DoctorPatient($existingUser, $currentUser);
                    $em->persist($doctorPatient);
                } else {
                    $view = $this->view(array(), 409);
                    return $this->handleView($view);
                }
            }

            $em->persist($invite);
            $this->get('invite_manager')->sendInvite($invite);
            $em->flush();
            $view = $this->view($invite, 200);

        } else {
            throw new NotFoundHttpException('Missing parameters');
        }

        return $this->handleView($view);
    }

    public function deleteAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $invite = $em->getRepository('UserBundle:Invite')->findOneBySlug($slug);
        if (!$invite) {
            throw new NotFoundHttpException('Invite not found.');
        }

        try {
            $em->remove($invite);
            $em->flush();

            $ret = array('slug' => $slug);
            $view = $this->view($ret);
        } catch (\Exception $e) {
            $ret = array('errors' => $e->getMessage());
            $view = $this->view($ret, 400);
        }

        return $this->handleView($view);
    }

    protected function getForm($invite = null, $method = 'POST')
    {
        return $this->createForm(new InviteFormType(), $invite, array('method' => $method));
    }
}
