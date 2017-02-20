<?php

namespace UserBundle\Controller;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;

use UserBundle\Entity\User;
use AppBundle\Entity\Document;

use UserBundle\Form\Type\ProfileInfoFormType;
use UserBundle\Form\Type\ProfileAddressFormType;
// use UserBundle\Form\Type\ProfilePasswordFormType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Controller managing the user profile
*/
class ProfileController extends FOSRestController
{
    /**
     * @Route("/profile/edit/{type}", name="profile_edit_info")
     * @Method({"POST"})
     */
    public function editProfileInfoAction(Request $request, $type)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        if ($type === 'info') {
            $form = $this->createForm(new ProfileInfoFormType());
        } else if ($type === 'address') {
            $form = $this->createForm(new ProfileAddressFormType());
        }
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

            $userManager->updateUser($user);

            $response = new JsonResponse(array('user' => $user));

            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            $view = $this->view($user, 200);
        } else {
            $view = $this->view($form);
        }

        return $this->handleView($view);
    }

    /**
     * @Route("/profile/avatar", name="profile_edit_avatar")
     * @Method({"POST"})
     */
    public function uploadAvatarAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $fs = $this->get('knp_gaufrette.filesystem_map')->get('default');
        $em = $this->getDoctrine()->getEntityManager();

        $file = $request->files->get('file');
        $fileName = $request->request->get('file');

        $createdDoc = new Document($fileName);
        $createdDoc->setFile($file);
        $createdDoc->setUser($user);
        $createdDoc->setFilename();

        // Take care of the upload of the file
        $fs->write(
            $createdDoc->getFullPath(),
            file_get_contents($createdDoc->file->getPathname())
        );

        $em->persist($createdDoc);

        // Attach it to the items
        $user->setAvatar($createdDoc);

        $em->flush();

        $view = $this->view($user, 200);

        return $this->handleView($view);
    }
}
