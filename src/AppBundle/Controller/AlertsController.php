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

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;

use JMS\Serializer\SerializationContext;
use AppBundle\Entity\Alert;
use AppBundle\Entity\Item;

use AppBundle\Form\Type\AlertFormType;

/**
 * @RouteResource("Alert")
 */
class AlertsController extends FOSRestController
{
    public function optionsAction()
    {}

    public function cgetAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $repository = $em->getRepository('AppBundle:Alert');

        $data = $repository->findAll();

        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    /**
    * @Put("/users/{userId}/alerts/close")
    */
    public function toggleCloseAlertAction(Request $request, $userId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        $user = $em->getRepository('UserBundle:User')->findOneById($userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        // $type = 'questions' || 'tasks'
        $type = $request->request->get('type');
        $alerts = $em->getRepository('AppBundle:Alert')->findNotClosedForUser($type, $user);

        foreach ($alerts as $alert) {
            $item = $alert->getItemActivity()->getItem();

            if ($type === 'questions' && $item->getType() === Item::TYPE_QUESTION) {
                $alert->setClosed(true);
            } else if ($type === 'tasks' && $item->getType() === Item::TYPE_TASK) {
                $alert->setClosed(true);
            }
        }

        $em->flush();

        $view = $this->view($alerts);

        return $this->handleView($view);
    }


    public function deleteAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $alert = $em->getRepository('AppBundle:Alert')->findOneBySlug($slug);
        if (!$alert) {
            throw new NotFoundHttpException('Alert not found.');
        }

        try {
            $em->remove($alert);
            $em->flush();

            $ret = array('slug' => $slug);
            $view = $this->view($ret);
        } catch (\Exception $e) {
            $ret = array('errors' => $e->getMessage());
            $view = $this->view($ret, 400);
        }

        return $this->handleView($view);
    }

    protected function getForm($alert = null, $method = 'POST')
    {
        $em = $this->getDoctrine()->getEntityManager();
        return $this->createForm(
            new AlertFormType($em), $alert, array('method' => $method)
        );
    }

}