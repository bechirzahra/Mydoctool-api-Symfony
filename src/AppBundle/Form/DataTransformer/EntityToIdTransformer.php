<?php
namespace AppBundle\Form\DataTransformer;

use AppBundle\Entity\Card;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EntityToIdTransformer implements DataTransformerInterface
{
    private $entityRepository;

    public function __construct($entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    public function transform($entity)
    {
        if (null === $entity) {
            return '';
        }

        return $entity->getId();
    }

    public function reverseTransform($id)
    {
        // no card number? It's optional, so that's ok
        if (!$id) {
            return;
        }

        $entity = $this->entityRepository->findOneById($id);

        if (null === $entity) {
            throw new TransformationFailedException(sprintf(
                'An entity with slug "%s" does not exist!',
                $id
            ));
        }

        return $entity;
    }
}