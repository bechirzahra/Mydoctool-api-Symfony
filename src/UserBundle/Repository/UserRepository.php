<?php

namespace UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UserBundle\Entity\User;

class UserRepository extends EntityRepository
{

    public function findLikeEmail($email)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('u')
            ->from('UserBundle:User', 'u')
            ->where('u.email LIKE ?1')
            ->setParameter(1, '%' . $email . '%');

        return $query->getQuery()->getResult();
    }

    public function findByActiveListing()
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('u')
            ->from('UserBundle:User', 'u')
            ->where('u.type = ?1')
            ->andWhere('u.followingListings IS NOT EMPTY')
            ->setParameters(array(
                1 => USER::USER_PATIENT,
            ));

        return $query->getQuery()->getResult();
    }

}