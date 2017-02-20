<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UserBundle\Entity\ItemActivity;

class ItemActivityRepository extends EntityRepository
{

    public function findCByItemSlug($slug, $user)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ia')
            ->from('AppBundle:ItemActivity', 'ia')
            ->join('ia.item', 'i')
            ->where('i.slug = ?1')
            ->andWhere('ia.user = ?2')
            ->setParameters(array(
                1 => $slug,
                2 => $user
            ));

        return $query->getQuery()->getResult();
    }

    public function findCByItemSlugNotDone($slug)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ia')
            ->from('AppBundle:ItemActivity', 'ia')
            ->join('ia.item', 'i')
            ->where('i.slug = ?1')
            ->andWhere('ia.createdAt = ia.updatedAt')
            ->setParameters(array(
                1 => $slug,
            ));

        return $query->getQuery()->getResult();
    }

    public function findCByUserNotDone($user)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ia')
            ->from('AppBundle:ItemActivity', 'ia')
            ->where('ia.user = ?1')
            ->andWhere('ia.createdAt = ia.updatedAt')
            ->setParameters(array(
                1 => $user,
            ));

        return $query->getQuery()->getResult();
    }
}