<?php
namespace AppBundle\Form\DataTransformer;

use AppBundle\Entity\Card;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EntitiesToSlugsTransformer implements DataTransformerInterface
{
    private $entityRepository;

    public function __construct($entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    public function transform($entities)
    {
        if (null === $entities || count($entities) === 0) {
            return [];
        }
        $ret = [];
        foreach ($entities as $entity) {
            $ret[] = $entity->getSlug();
        }
        return $ret;
    }

    public function reverseTransform($slugs)
    {
        // no card number? It's optional, so that's ok
        if (!$slugs) {
            return [];
        }

        $slugs = explode(',', $slugs);

        $entities = $this->entityRepository->findInSlugs($slugs);

        if (null === $entities) {
            throw new TransformationFailedException(sprintf(
                'An entity with slug "%s" does not exist!',
                $slug
            ));
        }

        return $entities;
    }
}