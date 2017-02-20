<?php

namespace UserBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessListener
{
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        // $data['token'] contains the JWT
        $data['data'] = array(
            'userId' => $user->getId(),
            'roles' => $user->getRoles(),
        );

        $event->setData($data);
    }
}