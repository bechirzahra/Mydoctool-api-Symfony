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
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Post;

use UserBundle\Entity\Organization;
use UserBundle\Entity\Invite;
use AppBundle\Entity\Document;
use UserBundle\Form\Type\OrganizationFormType;
use UserBundle\Form\Type\OrganizationInfoFormType;

/**
 * @RouteResource("Organization")
 */
class OrganizationsController extends FOSRestController
{
    //[OPTIONS]
    public function optionsAction()
    {}

    // [GET]
    public function cgetAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $repository = $em->getRepository('UserBundle:Organization');

        $organizations = $repository->findAll();
        $data = array('items' => $organizations);

        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    // [POST]
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $organization = new Organization();

        $form = $this->getForm();
        $form->setData($organization);

        $form->handleRequest($request);

        // $userEmail = $request->request->get('userEmail');
        // $userFirstname = $request->request->get('userFirstname');
        // $userLastname = $request->request->get('userLastname');

        if ($form->isValid()) {
            // When creating the Organization, we should invite the manager to join it.

            // Has the Manager already an account?
            // $existingUser = $em->getRepository('UserBundle:User')->findOneByEmail($userEmail);

            // $invite = new Invite($userEmail);
            // $moreData = array(
            //     'firstname' => $userFirstname,
            //     'lastname' => $userLastname
            // );
            // $invite->setMoreData($moreData);

            // if (!$existingUser) {
            //     $invite->setType(Invite::REGISTER_JOIN_ORGANIZATION_MANAGER);

            // } else {
            //     // If the user already has an organization, error.
            //     if ($existingUser->getOrganization() !== null) {
            //         throw new Exception("Cet e-mail est déjà associé à un utilisateur d'une autre Organisation.", 400);
            //     }

            //     $invite->setType(Invite::JOIN_ORGANIZATION_MANAGER);
            // }

            $fs = $this->get('knp_gaufrette.filesystem_map')->get('default');

            // Let's look for files
            $logo = $request->files->get('logoF');
            $image = $request->files->get('imageF');

            if (null !== $logo) {
                $createdDocLogo = new Document();
                $createdDocLogo->setFile($logo);
                $createdDocLogo->setFilename();

                // Take care of the upload of the file
                $fs->write(
                    $createdDocLogo->getFullPath(),
                    file_get_contents($createdDocLogo->file->getPathname())
                );

                $em->persist($createdDocLogo);
                $organization->setLogo($createdDocLogo);
            }

            if (null !== $image) {
                $createdDocImage = new Document();
                $createdDocImage->setFile($image);
                $createdDocImage->setFilename();

                // Take care of the upload of the file
                $fs->write(
                    $createdDocImage->getFullPath(),
                    file_get_contents($createdDocImage->file->getPathname())
                );

                $em->persist($createdDocImage);
                $organization->setImage($createdDocImage);
            }

            $em->persist($organization);

            // $invite->setFromOrganization($organization);
            // $em->persist($invite);

            // We send the invite
            // $this->get('invite_manager')->sendInvite($invite);

            $em->flush();

            $view = $this->view($organization);
        } else {
            $view = $this->view($form);
        }

        return $this->handleView($view);
    }

    public function getAction($organizationSlug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $organization = $em->getRepository('UserBundle:Organization')->findOneBySlug($organizationSlug);
        if (!$organization) {
            throw new NotFoundHttpException('Organization not found.');
        }

        $view = $this->view($organization);

        return $this->handleView($view);
    }

    /**
    * @POST("/organizations/{slug}")
    */
    public function updateAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $organization = $em->getRepository('UserBundle:Organization')->findOneBySlug($slug);
        if (!$organization) {
            throw new NotFoundHttpException('Organization not found.');
        }

        $form = $this->createForm(new OrganizationInfoFormType(), $organization, array('method' => 'POST'));
        $form->bind($request);

        if ($form->isValid()) {
            $fs = $this->get('knp_gaufrette.filesystem_map')->get('default');

            // Let's look for files
            $logo = $request->files->get('logoF');
            $image = $request->files->get('imageF');

            if (null !== $logo) {
                $createdDocLogo = new Document();
                $createdDocLogo->setFile($logo);
                $createdDocLogo->setFilename();

                // Take care of the upload of the file
                $fs->write(
                    $createdDocLogo->getFullPath(),
                    file_get_contents($createdDocLogo->file->getPathname())
                );

                $em->persist($createdDocLogo);
                $organization->setLogo($createdDocLogo);
            }

            if (null !== $image) {
                $createdDocImage = new Document();
                $createdDocImage->setFile($image);
                $createdDocImage->setFilename();

                // Take care of the upload of the file
                $fs->write(
                    $createdDocImage->getFullPath(),
                    file_get_contents($createdDocImage->file->getPathname())
                );

                $em->persist($createdDocImage);
                $organization->setImage($createdDocImage);
            }

            $em->flush();

            $view = $this->view($organization);
        } else {
            $view = $this->view($form);
        }

        return $this->handleView($view);
    }

    public function putAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $organization = $em->getRepository('UserBundle:Organization')->findOneBySlug($slug);
        if (!$organization) {
            throw new NotFoundHttpException('Organization not found.');
        }

        $form = $this->getForm($organization, 'PUT');
        $form->bind($request);

        if ($form->isValid()) {
            $em->flush();

            $view = $this->view($organization);
        } else {
            $view = $this->view($form);
        }

        return $this->handleView($view);
    }

    public function deleteAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $organization = $em->getRepository('UserBundle:Organization')->findOneBySlug($slug);
        if (!$organization) {
            throw new NotFoundHttpException('Organization not found.');
        }

        try {
            $em->remove($organization);
            $em->flush();

            $ret = array('slug' => $slug);
            $view = $this->view($ret);
        } catch (\Exception $e) {
            $ret = array('errors' => $e->getMessage());
            $view = $this->view($ret, 400);
        }

        return $this->handleView($view);
    }

    protected function getForm($organization = null, $method = 'POST')
    {
        return $this->createForm(new OrganizationFormType(), $organization, array('method' => $method));
    }
}