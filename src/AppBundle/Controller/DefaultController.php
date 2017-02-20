<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\Exception;
use JMS\Serializer\SerializationContext;

use UserBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;

use AppBundle\Entity\ItemActivity;

class DefaultController extends FOSRestController
{
    /**
     * @Route("/init/app", name="init_app")
     */
    public function initAppAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$user || false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('This user does not exists');
        }

        $items = array();
        $alerts = array();
        $invites = array();
        $templates = array();
        $organizations = array();
        $linkedUsersIds = array();
        $itemActivities = array();

        $messages = array_merge(
            $user->getSentMessages()->toArray(),
            $user->getReceivedMessages()->toArray()
        );

        $listings = $user->getListings();

        // If the current User is a Patient
        if ($user->getType() === User::USER_PATIENT) {
            $itemActivities = $user->getItemActivities();

            $knownItems = array();
            foreach ($itemActivities as $ia) {
                if (!isset($knownItems[$ia->getItem()->getId()])) {
                    $items[] = $ia->getItem();
                    $knownItems[$ia->getItem()->getId()] = 1;
                }
            }

            foreach ($user->getPatientDoctors() as $lU) {
                $linkedUsersIds[] = $lU->getDoctor()->getId();
                $organizations[] = $lU->getDoctor()->getOrganization();
            }

        }

        // If the current User is a Doctor
        else {
            $organizations[] = $user->getOrganization();

            // We fetch templates
            $templates = $em->getRepository('AppBundle:Listing')->findBy(array(
                'isTemplate' => true,
            ));

            // We create an item array by parsing every listing and then every template

            foreach ($listings as $listing) {
                $items = array_merge($items, $listing->getItems()->toArray());
            }

            foreach ($templates as $listing) {
                if (!$listings->contains($listing)) {
                    $items = array_merge($items, $listing->getItems()->toArray());
                }
            }

            // We should get every item activity of our patients
            foreach ($user->getDoctorPatients() as $u) {
                $iAs = $u->getPatient()->getItemActivities();

                foreach ($iAs as $iA) {
                    $iAListing = $iA->getListing();

                    if ($iAListing->getOwner()->getId() === $user->getId()) {
                        $itemActivities[] = $iA;
                    }
                }
            }

            // We create an array of our patients ids
            foreach ($user->getDoctorPatients() as $lU) {
                $linkedUsersIds[] = $lU->getPatient()->getId();
            }

            // Then, we can fetch there alerts
            $alerts = $em->getRepository('AppBundle:Alert')->findForUsers($linkedUsersIds);

            // We get every not-accepted invite sent by this user
            $invites = $em->getRepository('UserBundle:Invite')->findBy(array(
                'fromUser' => $user,
                'accepted' => false
            ));
        }

        $ret = array(
            'user' => $user,
            'messages' => $messages,
            'linkedUsers' => $this->getLinkedUsers($user)->getContent(),
            'categories' => $user->getCategories(),
            'listings' => $listings,
            'templates' => $templates,
            'items' => $items,
            'itemActivities' => $itemActivities,
            'alerts' => $alerts,
            'organizations' => $organizations,
            'invites' => $invites,
        );


        $view = $this->view($ret);

        return $this->handleView($view);
    }

    /**
    * This helper creates a serialized view of linked users of a User
    * i.e. patients for a doctor OR doctors for a patient
    */
    private function getLinkedUsers($user)
    {
        $ret = array();
        $attr = '';
        if ($user->getType() === User::USER_PATIENT) {
            $attr = 'getDoctor';
            $doctorPatients = $user->getPatientDoctors();
        } else {
            $attr = 'getPatient';
            $doctorPatients = $user->getDoctorPatients();
        }

        foreach ($doctorPatients as $doctorPatient) {
            $ret[] = array('user' => $doctorPatient->$attr(), 'favorite' => $doctorPatient->getFavorite(), 'enabled' => $doctorPatient->getPatient()->isEnabled());
        }

        $view = $this->view($ret);
        $context = SerializationContext::create()->setGroups(array('simple'));
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * @Route("/init/fixtures", name="init_fixtures")
     */
    public function fixturesAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $today = new \DateTime();

        for ($i=2; $i < 100; $i++) {
            $user = $em->getRepository('UserBundle:User')->findOneById(10);
            $item = $em->getRepository('AppBundle:Item')->findOneById(28);

            $createdDate = clone $today;
            $createdDate->modify('-' . $i*3 . ' days');

            $updatedDate = clone $createdDate;
            $updatedDate->modify('+' . rand(0, 3) . ' days');

            $iA = new ItemActivity($user, $item);
            $iA->setAnswerInt(rand(80, 120));
            $iA->setCreatedAt($createdDate);
            $iA->setUpdatedAt($updatedDate);
            $em->persist($iA);
        }

        $em->flush();

        $view = $this->view('ok');
        return $this->handleView($view);
    }

    /**
     * @Route("/init/email", name="init_email")
     */
    public function emailAction()
    {
        $mailer = $this->get('mdt.mailer');

        $subject = 'Hello test';
        $fromEmail = array('support@mydoctool.com' => 'MyDocTool');
        $toEmail = 'thomas.pilvee@gmail.com';
        $templateName = 'UserBundle:Emails:testEmail.html.twig';
        $mailer->sendEmail($subject, $fromEmail, $toEmail, $templateName);

        $view = $this->view('ok');
        return $this->handleView($view);
    }
}
