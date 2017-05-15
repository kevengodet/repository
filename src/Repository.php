<?php

namespace Adagio\Repository;

use Adagio\Repository\Exception\NotFound;
use Adagio\DataStore\DataStore;
use Adagio\Rad\Traits\GuessIdentifier;

final class Repository
{
    use GuessIdentifier;

    const CLASS_PROPERTY = '__entity_class__';

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
            throw NotFound::fromId($identifier);
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
        $data[self::CLASS_PROPERTY] = get_class($entity);

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
        $className = $data[self::CLASS_PROPERTY];
        unset($data[self::CLASS_PROPERTY]);

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
}