<?php

namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Post;
use AppBundle\Entity\UserListing;

class UsersController extends FOSRestController
{
    // "options_users" [OPTIONS] /users
    public function optionsUsersAction()
    {}

    // "get_users"     [GET] /users
    public function getUsersAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $repository = $em->getRepository('UserBundle:User');

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $page = (int) $request->query->get('page');
        $resultsPerPage = (int) $request->query->get('resultsPerPage');
        $filter = htmlspecialchars($request->query->get('filter'));

        if (!empty($page) && !empty($resultsPerPage)) {
            $paginator = $this->get('knp_paginator');

            if (!empty($filter)) {
                $users = $repository->findLikeEmail($filter);
                $data = $paginator->paginate(
                    $users, $page, $resultsPerPage
                );
            } else {
                $users = $repository->findAll();
                $data = $paginator->paginate(
                    $users, $page, $resultsPerPage
                );
            }
        } else {
            $users = $repository->findAll();
            $data = array('items' => $users);
        }

        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    // "patch_users"   [PATCH] /users
    public function patchUsersAction()
    {}

    // "get_user"      [GET] /users/{id}
    public function getUserAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $user = $em->getRepository('UserBundle:User')->findOneById($id);

        if (!$user) {
            throw new NotFoundHttpException("User for id " . $id . " not found.");
        }

        $view = $this->view($user, 200);

        return $this->handleView($view);
    }

    /**
    * @Post("/users/{slug}")
    */
    public function updateUserAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();

        if ($slug === 'me') {

            $currentUser = $this->get('security.token_storage')->getToken()->getUser();

            $firstname = htmlspecialchars($request->request->get('firstname'));
            $lastname = htmlspecialchars($request->request->get('lastname'));
            $receiveNewsletter = htmlspecialchars($request->request->get('receive-newsletter'));
            $newEmail = htmlspecialchars($request->request->get('email'));

            if (!empty($firstname)) {
                $currentUser->setFirstname($firstname);
            }
            if (!empty($lastname)) {
                $currentUser->setLastname($lastname);
            }

            if ($receiveNewsletter === 'false') {
                $currentUser->setReceiveNewsletter(false);
            } else {
                $currentUser->setReceiveNewsletter(true);
            }

            if (!empty($newEmail) && $newEmail !== $currentUser->getEmail()) {
                $existingUser = $em->getRepository('UserBundle:User')->findOneByEmail($newEmail);

                if (!$existingUser) {
                    $currentUser->setEmail($newEmail);
                    $currentUser->setUsername($newEmail);
                    $currentUser->setUsernameCanonical($newEmail);
                } else {
                    throw new HttpException(409, 'Cette addresse email existe déjà.');
                }
            }

            $em->flush();

            $view = $this->view($currentUser, 200);
        } else {
            $currentUser = $this->get('security.token_storage')->getToken()->getUser();
            $user = $em->getRepository('UserBundle:User')->findOneById($slug);

            if (!$user) {
                throw new NotFoundHttpException();
            }

            $doctorPatients = $currentUser->getDoctorPatients();
            $handle = false;
            foreach ($doctorPatients as $doctorPatient) {
                if ($doctorPatient->getPatient() === $user) {
                    $handle = true;
                }
            }

            if ($handle || $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                $userMaidenname = htmlspecialchars($request->request->get('maidenName'));
                $birthdayDay = htmlspecialchars($request->request->get('birthdayDay'));
                $birthdayMonth = htmlspecialchars($request->request->get('birthdayMonth'));
                $birthdayYear = htmlspecialchars($request->request->get('birthdayYear'));
                $gender = htmlspecialchars($request->request->get('gender'));
                $ipp = htmlspecialchars($request->request->get('ipp'));
                $phoneNumber = htmlspecialchars($request->request->get('phoneNumber'));
                $interventionInfo = htmlspecialchars($request->request->get('interventionInfo'));
                $interventionDay = htmlspecialchars($request->request->get('interventionDay'));
                $interventionMonth = htmlspecialchars($request->request->get('interventionMonth'));
                $interventionYear = htmlspecialchars($request->request->get('interventionYear'));
                $otherInfo = htmlspecialchars($request->request->get('otherInfo'));

                $user->setMaidenname($userMaidenname);
                $user->setBirthdayDay($birthdayDay);
                $user->setBirthdayMonth($birthdayMonth);
                $user->setBirthdayYear($birthdayYear);
                $user->setGender($gender);
                $user->setIpp($ipp);
                $user->setPhoneNumber($phoneNumber);
                $user->setInterventionInfo($interventionInfo);
                $user->setInterventionDay($interventionDay);
                $user->setInterventionMonth($interventionMonth);
                $user->setInterventionYear($interventionInfo);
                $user->setOtherInfo($otherInfo);

                $em->flush();
                $view = $this->view($user, 200);
            } else {
                throw new AccessDeniedException();
            }
        }

        return $this->handleView($view);
    }

    /**
    * @Post("/users/me/avatar")
    */
    public function updateAvatarAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $fs = $this->get('knp_gaufrette.filesystem_map')->get('default');

        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        $file = $request->files->get('file');

        if (null === $file) {
            throw new HttpException(400, 'Avatar not found.');
        }

        $ext = '.' . $file->guessExtension();
        $filename = uniqid() . $ext;
        $fullpath = '/' . $currentUser->getFolder() . '/' . $filename;

        $fs->write(
            $fullpath,
            file_get_contents($file->getPathname())
        );

        $absolutePath = $request->getUriForPath('/uploads' . $fullpath, true);
        $currentUser->setAvatar($absolutePath);

        $em->flush();

        $view = $this->view($currentUser, 200);

        return $this->handleView($view);
    }

    /**
    * @Post("/users/{userId}/favorite")
    */
    public function toggleFavoriteAction(Request $request, $userId)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        $patient = $em->getRepository('UserBundle:User')->findOneById($userId);

        if (!$patient) {
            throw new NotFoundHttpException('user not found');
        }

        $doctorPatient = $em->getRepository('UserBundle:DoctorPatient')->findOneBy(array(
            'doctor' => $currentUser,
            'patient' => $patient
        ));

        if (!$doctorPatient) {
            throw new NotFoundHttpException('doctor patient not found');
        }

        $doctorPatient->setFavorite(!$doctorPatient->getFavorite());
        $em->flush();

        $view = $this->view(array('user' => $patient, 'favorite' => $doctorPatient->getFavorite()));
        return $this->handleView($view);
    }

    /**
    * @Post("/users/{userId}/listings/{listingSlug}")
    */
    public function toggleUserListingAction(Request $request, $userId, $listingSlug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        $patient = $em->getRepository('UserBundle:User')->findOneById($userId);

        if (!$patient) {
            throw new NotFoundHttpException('user not found');
        }

        $listing = $em->getRepository('AppBundle:Listing')->findOneBySlug($listingSlug);

        if (!$listing) {
            throw new NotFoundHttpException('listing not found');
        }

        $userListing = $em->getRepository('AppBundle:UserListing')->findOneBy(array(
            'patient' => $patient,
            'listing' => $listing
        ));

        // If the userlisting does not exists, we create it.
        if (!$userListing) {
            $userListing = new UserListing($patient, $listing);
            $startDay = htmlspecialchars($request->request->get('startDay'));
            $startMonth = htmlspecialchars($request->request->get('startMonth'));
            $startYear = htmlspecialchars($request->request->get('startYear'));

            $startDate = new \DateTime($startYear . '-' . $startMonth . '-' . $startDay);
            $userListing->setCreatedAt($startDate);

            $em->persist($userListing);
        }
        // Else, we remove the current listing
        else {
            $em->remove($userListing);
        }

        $em->flush();

        $ret = array(
            'user_id' => $userId,
            'listing_slug' => $listingSlug,
            'userListing' => $userListing
        );

        $view = $this->view($ret);
        return $this->handleView($view);
    }

    /**
    * @Post("/users/{userId}/remove-patient")
    */
    public function removeUserPatientAction(Request $request, $userId)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $doctor = $this->get('security.token_storage')->getToken()->getUser();
        $patient = $em->getRepository('UserBundle:User')->findOneById($userId);

        if (!$patient) {
            throw new NotFoundHttpException('user not found');
        }

        $count = array(
            'userListing' => 0,
            'iAs' => 0
        );


        $doctorPatient = $em->getRepository('UserBundle:DoctorPatient')->findOneBy(array(
            'patient' => $patient,
            'doctor' => $doctor,
        ));

        if (!$doctorPatient) {
            throw new AccessDeniedException('Not your patient');
        }

        // Now that we have a Doctor, a Patient, and we now they were together
        // We can remove the Patient from the doctor.

        // We remove the DoctorPatient Link, the UserListing and every linked ItemActivity
        $userListings = $patient->getUserListings();
        foreach ($userListings as $uL) {
            if ($uL->getListing()->getOwner() === $doctor) {
                $count['userListing']++;
                $em->remove($uL);
            }
        }

        $iAs = $em->getRepository('AppBundle:ItemActivity')->findBy(array('user' => $patient));
        foreach ($iAs as $iA) {
            if ($iA->getItem()->getListing()->getOwner() === $doctor) {
                $count['iAs']++;
                $em->remove($iA);
            }
        }

        $messagesA = $em->getRepository('AppBundle:Message')->findBy(array(
            'fromUser' => $patient,
            'toUser' => $doctor,
        ));

        foreach ($messagesA as $m) {
            $em->remove($m);
        }

        $messagesB = $em->getRepository('AppBundle:Message')->findBy(array(
            'fromUser' => $doctor,
            'toUser' => $patient,
        ));

        foreach ($messagesB as $m) {
            $em->remove($m);
        }

        $em->remove($doctorPatient);

        $em->flush();

        $ret = array(
            'user_id' => $userId,
            'count' => $count,
        );

        $view = $this->view($ret);
        return $this->handleView($view);
    }

}