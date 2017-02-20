<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Post;

use AppBundle\Entity\Document;
use AppBundle\Form\Type\DocumentFormType;

/**
 * @RouteResource("Document")
 */
class DocumentsController extends FOSRestController
{
    public function optionsAction()
    {} //[OPTIONS]


    public function cgetAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $repository = $em->getRepository('AppBundle:Document');
        $fs = $this->get('knp_gaufrette.filesystem_map')->get('default');

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $documents = $repository->findAll();
        $data = array('items' => $documents);

        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $document = new Document();

        $form = $this->getForm();
        $form->setData($document);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $document->setFilename();

            $fs = $this->get('knp_gaufrette.filesystem_map')->get('default');

            $fs->write(
                $document->getFullPath(),
                file_get_contents($document->file->getPathname())
            );

            $em->persist($document);
            $em->flush();

            $view = $this->view($document);
        } else {
            $view = $this->view($form);
        }

        return $this->handleView($view);
    }

    public function cpatchAction()
    {} // "patch_users"   [PATCH] /users

    public function getAction($documentSlug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $document = $em->getRepository('AppBundle:Document')->findOneBySlug($documentSlug);
        if (!$document) {
            throw new NotFoundHttpException('Document not found.');
        }

        $view = $this->view($document);

        return $this->handleView($view);
    } // "get_user"      [GET] /users/{slug}


    /**
    * @Post("/documents/{slug}/put")
    */
    public function customPutAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $document = $em->getRepository('AppBundle:Document')->findOneBySlug($slug);
        if (!$document) {
            throw new NotFoundHttpException('Document not found.');
        }

        $form = $this->getForm($document);

        $form->handleRequest($request);
        // $form->submit($request);

        if ($form->isValid()) {

            if ($document->file !== null && $document->file !== '') {
                $document->setFilename();

                $fs = $this->get('knp_gaufrette.filesystem_map')->get('default');

                $fs->write(
                    $document->getFullPath(),
                    file_get_contents($document->file->getPathname())
                );
            }

            $em->flush();

            $view = $this->view($document);
        } else {
            $view = $this->view($form);
        }

        return $this->handleView($view);
    }


    public function deleteAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $document = $em->getRepository('AppBundle:Document')->findOneBySlug($slug);
        if (!$document) {
            throw new NotFoundHttpException('Document not found.');
        }

        try {
            $em->remove($document);
            $em->flush();

            $ret = array('slug' => $slug);
            $view = $this->view($ret);
        } catch (\Exception $e) {
            $ret = array('errors' => $e->getMessage());
            $view = $this->view($ret, 400);
        }

        return $this->handleView($view);
    }

    protected function getForm($document = null, $method = 'POST')
    {
        $em = $this->getDoctrine()->getEntityManager();
        return $this->createForm(new DocumentFormType($em), $document, array('method' => $method));
    }

}