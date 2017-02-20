<?php

namespace AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\RestBundle\Controller\FOSRestController;
use UserBundle\Entity\User;


class DefaultController extends FOSRestController
{
    /**
     * @Route("/init", name="admin_init")
     */
    public function initAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $organizations = $em->getRepository('UserBundle:Organization')->findAll();
        $users = $em->getRepository('UserBundle:User')->findAll();
        $invites = $em->getRepository('UserBundle:Invite')->findAll();
        $listings = $em->getRepository('AppBundle:Listing')->findAll();

        $linkedUsersIds = array();

        foreach ($users as $user) {
            $lUs = $user->getPatientDoctors();
            $linkedUsersIds[$user->getId()] = array();

            $attr = '';
            if ($user->getType() === User::USER_PATIENT) {
                $attr = 'getDoctor';
                $doctorPatients = $user->getPatientDoctors();
            } else {
                $attr = 'getPatient';
                $doctorPatients = $user->getDoctorPatients();
            }

            foreach ($doctorPatients as $doctorPatient) {
                $linkedUsersIds[$user->getId()][] = $doctorPatient->$attr()->getId();
            }
        }

        $ret = array(
            'organizations' => $organizations,
            'users' => $users,
            'invites' => $invites,
            'listings' => $listings,
            'linkedUsers' => $linkedUsersIds,
        );

        $view = $this->view($ret);

        return $this->handleView($view);
    }
}
