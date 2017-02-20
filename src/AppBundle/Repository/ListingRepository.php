<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UserBundle\Entity\User;

class ListingRepository extends EntityRepository
{
    public function findLikeName($name)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from('AppBundle:Listing', 'c')
            ->where('c.name LIKE ?1')
            ->setParameter(1, '%' . $name . '%');

        return $query->getQuery()->getResult();
    }

    public function findIn($slugs)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from('AppBundle:Listing', 'c')
            ->where('c.slug IN (?1)')
            ->setParameter(1, $slugs);

        return $query->getQuery()->getResult();
    }
}