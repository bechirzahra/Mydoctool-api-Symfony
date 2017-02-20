<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Item;
use AppBundle\Entity\Question;

class ItemRepository extends EntityRepository
{

    public function findTasksAndTexts()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $tasks = $qb->select('i')->from('AppBundle:Task', 'i')->getQuery()->getResult();
        $texts = $qb->select('q')->from('AppBundle:Question', 'q')
            ->where('q.questionType = ?1')
            ->setParameter(1, Question::QUESTION_TEXT)
            ->getQuery()->getResult();

        return array_merge($tasks, $texts);
    }
}