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

use JMS\Serializer\SerializationContext;

use AppBundle\Entity\Message;
use AppBundle\Form\Type\MessageFormType;
use AppBundle\Entity\Document;

/**
 * @RouteResource("Message")
 */
class MessagesController extends FOSRestController
{
    public function optionsAction()
    {}

    public function cgetAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $repository = $em->getRepository('AppBundle:Message');

        $messages = $repository->findAll();
        $data = array('items' => $messages);

        $view = $this->view($data, 200);

        return $this->handleView($view);
    }

    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $fs = $this->get('knp_gaufrette.filesystem_map')->get('default');

        $newFiles = array();
        $message = new Message();

        $form = $this->getForm();
        $form->setData($message);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $files = $request->files->get('files');
            $filenames = $request->request->get('files');

            if (isset($files) && count($files) > 0) {

                foreach ($files as $k => $uploadedFile) {
                    $doc = new Document();
                    $doc->file = $uploadedFile;
                    $doc->setFilename();

                    $doc->setName(htmlspecialchars($filenames[$k]));

                    $message->addDocument($doc);
                    $message->getFromUser()->addDocument($doc);

                    $fs->write(
                        $doc->getFullPath(),
                        file_get_contents($doc->file->getPathname())
                    );

                    $em->persist($doc);
                    $newFiles[] = $doc;
                }
            }

            $em->persist($message);
            $em->flush();

            $ret = array(
                'message' => $message,
                'documents' => $newFiles,
            );

            $view = $this->view($ret);
        } else {
            $view = $this->view($form);
        }

        return $this->handleView($view);
    }

    /**
    * @Post("/messages/read/{slug}")
    */
    public function readMessageAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $message = $em->getRepository('AppBundle:Message')->findOneBySlug($slug);
        if (!$message) {
            throw new NotFoundHttpException('Message not found.');
        }

        $message->setRead(true);
        $em->flush();

        $view = $this->view($message);
        return $this->handleView($view);
    }


    public function getAction($messageSlug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $message = $em->getRepository('AppBundle:Message')->findOneBySlug($messageSlug);
        if (!$message) {
            throw new NotFoundHttpException('Message not found.');
        }

        $view = $this->view($message);

        return $this->handleView($view);
    }

    public function putAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $message = $em->getRepository('AppBundle:Message')->findOneBySlug($slug);
        if (!$message) {
            throw new NotFoundHttpException('Message not found.');
        }

        $form = $this->getForm($message, 'PUT');
        $form->bind($request);

        if ($form->isValid()) {
            $em->flush();

            $view = $this->view($message);
        } else {
            $view = $this->view($form);
        }

        return $this->handleView($view);
    }


    public function deleteAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $message = $em->getRepository('AppBundle:Message')->findOneBySlug($slug);
        if (!$message) {
            throw new NotFoundHttpException('Message not found.');
        }

        try {
            $em->remove($message);
            $em->flush();

            $ret = array('slug' => $slug);
            $view = $this->view($ret);
        } catch (\Exception $e) {
            $ret = array('errors' => $e->getMessage());
            $view = $this->view($ret, 400);
        }

        return $this->handleView($view);
    }

    protected function getForm($message = null, $method = 'POST')
    {
        return $this->createForm(new MessageFormType($this->getDoctrine()->getEntityManager()), $message, array('method' => $method));
    }

}