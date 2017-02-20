<?php

namespace UserBundle\Security\Provider;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use FOS\UserBundle\Model\User;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Propel\User as PropelUser;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;

class MyProvider extends BaseClass implements UserProviderInterface
{
    private $em;

    function __construct(UserManagerInterface $userManager, array $properties, $em)
    {
        parent::__construct($userManager, $properties);
        $this->em = $em;
    }

    public function connect(SecurityUserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();
        //on connect - get the access token and the user ID
        $service = $response->getResourceOwner()->getName();
        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'_id';
        $setter_token = $setter.'AccessToken';
        //we "disconnect" previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }
        //we connect current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        $this->userManager->updateUser($user);
    }


    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $idByEmail = false;
        $identifier = $response->getUsername();
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $identifier));

        if (null === $user) {
            $user = $this->userManager->findUserByEmail($response->getEmail());
            $idByEmail = true;
        }

        //when the user is registrating
        if (null === $user) {
            $service = $response->getResourceOwner()->getName();
            $setter = 'set'.ucfirst($service);
            $setter_id = $setter.'_id';
            $setter_token = $setter.'AccessToken';
            // create new user here
            $user = $this->userManager->createUser();

            // if($service == 'google') {
            //     $user->setGoogleAvatar($response->getProfilePicture());
            // }

            $user->$setter_id($identifier);
            $user->$setter_token($response->getAccessToken());

            $user->setUsername($response->getEmail());
            $user->setEmail($response->getEmail());
            $user->setPassword($identifier);
            $user->setEnabled(true);
            $user->addRole($user::ROLE_DEFAULT);

            return $user;
        }

        //if user exists - go with the HWIOAuth way
        if ($idByEmail) {
            $user = parent::loadUserByUsername($user->getUsername());
        }
        else {
            $user = parent::loadUserByOAuthUserResponse($response);
        }

        $serviceName = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($serviceName);
        $setter_id = $setter.'_id';
        $setter_token = $setter.'AccessToken';

        //update access token
        // if ($serviceName === 'google') {
        //     $user->setGoogleAvatar($response->getProfilePicture());
        // }

        $user->$setter_id($identifier);
        $user->$setter_token($response->getAccessToken());
        $this->userManager->updateUser($user);

        return $user;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->userManager->findUserByEmail($username);

        if (!$user) {
            $user = $this->userManager->findUserByUsername($username);
            if (!$user) {
                throw new UsernameNotFoundException(sprintf('No user with e-mail "%s" was found.', $username));
            }
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(SecurityUserInterface $user)
    {
        if (!$user instanceof User && !$user instanceof PropelUser) {
            throw new UnsupportedUserException(sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', get_class($user)));
        }

        if (null === $reloadedUser = $this->userManager->findUserBy(array('id' => $user->getId()))) {
            throw new UsernameNotFoundException(sprintf('User with ID "%d" could not be reloaded.', $user->getId()));
        }

        return $reloadedUser;
    }

    public function supportsClass($class)
    {
        return $this->userManager->supportsClass($class);
    }
}