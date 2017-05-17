<?php

namespace Adagio\Repository;

use Adagio\Repository\Exception\NotFound;
use Adagio\DataStore\DataStore;
use Adagio\Rad\Traits\GuessIdentifier;

const CLASS_PROPERTY = '__entity_class__';

trait RepositoryTrait
{
    use GuessIdentifier;

    /**
     *
     * @var DataStore
     */
    private $store;

    /**
     *
     * @param DataStore $store
     */
    public function __construct(DataStore $store)
    {
        $this->store = $store;
    }

    /**
     *
     * @param array|object $entity
     * @param string $identifier If not provided, it will be guessed and returned by the function
     *
     * @return string Identifier used
     */
    public function store($entity, $identifier = null)
    {
        if (is_null($identifier)) {
            $identifier = $this->guessIdentifierOrHash($entity);
        }

        $this->store->store($this->normalize($entity), $identifier);

        return $identifier;
    }

    /**
     *
     * @param object|array $entity
     *
     * @return bool
     */
    public function hasEntity($entity)
    {
        return $this->store->has($this->guessIdentifierOrHash($entity));
    }

    /**
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return $this->store->has($identifier);
    }

    /**
     *
     * @param string $identifier
     *
     * @return mixed
     *
     * @throws NotFound
     */
    public function get($identifier)
    {
        if (!$this->store->has($identifier)) {
            throw NotFound::fromIdentifier($identifier);
        }

        return $this->denormalize($this->store->get($identifier));
    }

    /**
     *
     * @param string $identifier
     */
    public function remove($identifier)
    {
        $this->store->remove($identifier);
    }

    /**
     *
     * @param string $property
     * @param mixed $value
     * @param string $comparator
     *
     * @return object[]
     */
    public function findBy($property, $value, $comparator = '=')
    {
        return $this->denormalizeArray($this->store->findBy($property, $value, $comparator));
    }

    /**
     *
     * @param string $property
     * @param mixed $value
     * @param string $comparator
     *
     * @return object
     *
     * @throws NotFound
     */
    public function findOneBy($property, $value, $comparator = '=')
    {
        foreach ($this->store->findBy($property, $value, $comparator) as $result) {
            return $this->denormalize($result);
        }

        throw NotFound::fromProperty($property, $comparator, $result);
    }

    /**
     *
     * @return object[]
     */
    public function findAll()
    {
        return $this->denormalizeArray($this->store->findAll());
    }

    /**
     *
     * @param object $entity
     *
     * @return array
     */
    private function normalize($entity)
    {
        // Normalize $entity
        $data = [];
        foreach ((array) $entity as $k => $v) {
            if (false !== $pos = strrpos($k, "\0")) {
                $data[substr($k, $pos + 1)] = $v;
            } else {
                $data[$k] = $v;
            }
        }
        $data[CLASS_PROPERTY] = get_class($entity);

        return $data;
    }

    /**
     *
     * @param array $data
     *
     * @return entity
     */
    private function denormalize(array $data)
    {
        $className = $data[CLASS_PROPERTY];
        unset($data[CLASS_PROPERTY]);

        $r = new \ReflectionClass($className);
        $entity = $r->newInstanceWithoutConstructor();
        foreach ($data as $key => $value) {
            if ($r->hasProperty($key)) {
                $p = $r->getProperty($key);
                $p->setAccessible(true);
                $p->setValue($entity, $value);
            } else {
                $entity->$key = $value;
            }
        }

        return $entity;
    }

    /**
     *
     * @param array $results
     *
     * @return array
     */
    private function denormalizeArray($results)
    {
        $entities = [];
        foreach ($results as $result) {
            $entities[] = $this->denormalize($result);
        }

        return $entities;
    }
}
