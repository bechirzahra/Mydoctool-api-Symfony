<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UserBundle\Entity\User;

class CategoryRepository extends EntityRepository
{
    public function findLikeName($name)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from('AppBundle:Category', 'c')
            ->where('c.name LIKE ?1')
            ->setParameter(1, '%' . $name . '%');

        return $query->getQuery()->getResult();
    }
}