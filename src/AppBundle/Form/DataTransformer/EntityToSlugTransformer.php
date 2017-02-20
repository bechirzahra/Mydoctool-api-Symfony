<?php
namespace AppBundle\Form\DataTransformer;

use AppBundle\Entity\Card;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EntityToSlugTransformer implements DataTransformerInterface
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

        return $entity->getSlug();
    }

    public function reverseTransform($slug)
    {
        // no card number? It's optional, so that's ok
        if (!$slug) {
            return;
        }

        $entity = $this->entityRepository->findOneBySlug($slug);

        if (null === $entity) {
            throw new TransformationFailedException(sprintf(
                'An entity with slug "%s" does not exist!',
                $slug
            ));
        }

        return $entity;
    }
}