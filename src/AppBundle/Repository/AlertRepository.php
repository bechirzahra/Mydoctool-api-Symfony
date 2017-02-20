<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Alert;

class AlertRepository extends EntityRepository
{
    public function findIn($slugs)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('a')
            ->from('AppBundle:Alert', 'a')
            ->where('a.slug IN (?1)')
            ->setParameter(1, $slugs);

        return $query->getQuery()->getResult();
    }

    public function findNotClosed($type)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('a')
            ->from('AppBundle:Alert', 'a')
            ->where('a.closed = ?2')
            ->setParameters(array(
                2 => false
            ));
        ;

        return $query->getQuery()->getResult();
    }

    public function findNotClosedForUser($type, $user)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('a')
            ->from('AppBundle:Alert', 'a')
            ->join('a.itemActivity', 'ia')
            ->where('a.closed = ?2')
            ->andWhere('ia.user = ?1')
            ->setParameters(array(
                1 => $user,
                2 => false
            ));
        ;

        return $query->getQuery()->getResult();
    }

    public function findForUsers($userIds)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('a')
            ->from('AppBundle:Alert', 'a')
            ->join('a.itemActivity', 'ia')
            ->join('ia.user', 'u')
            ->where('u.id IN (?1)')
            ->setParameters(array(
                1 => $userIds,
            ));
        ;

        return $query->getQuery()->getResult();
    }
}