<?php

namespace Omouren\DocumentIdFormBundle\DataTransformer;

use Doctrine\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * DocumentToIdTransformer class
 *
 * @author Omouren <mouren.olivier@gmail.com>
 */
class DocumentToIdTransformer implements DataTransformerInterface
{
    private $dm;
    private $class;
    private $property;
    private $queryBuilder;
    private $multiple;
    private $unitOfWork;

    public function __construct(DocumentManager $dm, $class, $property, $queryBuilder, $multiple)
    {
        if (!(null === $queryBuilder || $queryBuilder instanceof Builder || $queryBuilder instanceof \Closure)) {
            throw new UnexpectedTypeException($queryBuilder, 'Doctrine\MongoDB\Query\Builder or \Closure');
        }
        if (null === $class) {
            throw new UnexpectedTypeException($class, 'string');
        }
        $this->dm = $dm;
        $this->unitOfWork = $this->dm->getUnitOfWork();
        $this->class = $class;
        $this->queryBuilder = $queryBuilder;
        $this->multiple = $multiple;
        if ($property) {
            $this->property = $property;
        }
    }

    public function transform($data)
    {
        if (null === $data) {
            return null;
        }
        if (!$this->multiple) {
            return $this->transformSingleEntity($data);
        }
        $return = [];
        foreach ($data as $element) {
            $return[] = $this->transformSingleEntity($element);
        }

        return implode(', ', $return);
    }

    private function splitData($data)
    {
        return is_array($data) ? $data : explode(',', $data);
    }

    private function transformSingleEntity($data)
    {
        return $data;
    }

    public function reverseTransform($data)
    {
        if (!$data) {
            return null;
        }
        if (!$this->multiple) {
            return $this->reverseTransformSingleEntity($data);
        }
        $return = [];
        foreach ($this->splitData($data) as $element) {
            $return[] = $this->reverseTransformSingleEntity($element);
        }

        return $return;
    }

    protected function reverseTransformSingleEntity($data)
    {
        $dm = $this->dm;
        $class = $this->class;
        $repository = $dm->getRepository($class);
        $qb = $this->queryBuilder;
        if ($qb) {
            if ($qb instanceof \Closure) {
                $qb = $qb($repository, $data);
            }
            try {
                $result = $qb->getQuery()->getSingleResult();
            } catch (\Exception $e) {
                $result = null;
            }
        } else {
            if ($this->property) {
                $result = $repository->findOneBy([$this->property => $data]);
            } else {
                $result = $repository->find($data);
            }
        }
        if (!$result) {
            throw new TransformationFailedException('Can not find document');
        }

        return $result;
    }
}
