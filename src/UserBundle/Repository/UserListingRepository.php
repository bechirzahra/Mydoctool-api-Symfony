<?php

namespace UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UserBundle\Entity\UserListing;

class UserListingRepository extends EntityRepository
{

    public function findByPatientAndActiveListing()
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ul')
            ->from('AppBundle:UserListing', 'ul')
            ->join('ul.user', 'u')
            ->where('u.type = ?1')
            ->setParameters(array(
                1 => USER::USER_PATIENT,
            ));

        return $query->getQuery()->getResult();
    }

}