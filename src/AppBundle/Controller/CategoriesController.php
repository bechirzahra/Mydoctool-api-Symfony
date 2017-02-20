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

use AppBundle\Entity\Category;
use AppBundle\Form\Type\CategoryFormType;

/**
 * @RouteResource("Category")
 */
class CategoriesController extends FOSRestController
{
    public function optionsAction()
    {}

    public function cgetAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $repository = $em->getRepository('AppBundle:Category');

        $categories = $repository->findAll();
        $data = array('items' => $categories);

        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $category = new Category();

        $form = $this->getForm();
        $form->setData($category);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $user->addCategory($category);

            $em->persist($category);
            $em->flush();

            $view = $this->view($category);
        } else {
            $view = $this->view($form);
        }

        return $this->handleView($view);
    }

    public function getAction($categorySlug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $category = $em->getRepository('AppBundle:Category')->findOneBySlug($categorySlug);
        if (!$category) {
            throw new NotFoundHttpException('Category not found.');
        }

        $view = $this->view($category);

        return $this->handleView($view);
    }


    public function putAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $category = $em->getRepository('AppBundle:Category')->findOneBySlug($slug);
        if (!$category) {
            throw new NotFoundHttpException('Category not found.');
        }

        $form = $this->getForm($category, 'PUT');
        $form->bind($request);

        if ($form->isValid()) {
            $em->flush();

            $view = $this->view($category);
        } else {
            $view = $this->view($form);
        }

        return $this->handleView($view);
    }


    public function deleteAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $category = $em->getRepository('AppBundle:Category')->findOneBySlug($slug);
        if (!$category) {
            throw new NotFoundHttpException('Category not found.');
        }

        try {
            $em->remove($category);
            $em->flush();

            $ret = array('slug' => $slug);
            $view = $this->view($ret);
        } catch (\Exception $e) {
            $ret = array('errors' => $e->getMessage());
            $view = $this->view($ret, 400);
        }

        return $this->handleView($view);
    }

    protected function getForm($category = null, $method = 'POST')
    {
        return $this->createForm(new CategoryFormType(), $category, array('method' => $method));
    }

}