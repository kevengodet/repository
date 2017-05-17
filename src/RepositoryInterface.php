<?php

namespace Adagio\Repository;

use Adagio\Repository\Exception\NotFound;

interface RepositoryInterface
{
    /**
     *
     * @param array|object $entity
     * @param string $identifier If not provided, it will be guessed and returned by the function
     *
     * @return string Identifier used
     */
    public function store($entity, $identifier = null);

    /**
     *
     * @param object|array $entity
     *
     * @return bool
     */
    public function hasEntity($entity);

    /**
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier);

    /**
     *
     * @param string $identifier
     *
     * @return mixed
     *
     * @throws NotFound
     */
    public function get($identifier);

    /**
     *
     * @param string $identifier
     */
    public function remove($identifier);
    /**
     *
     * @param string $property
     * @param mixed $value
     * @param string $comparator
     *
     * @return object
     */
    public function findBy($property, $value, $comparator = '=');

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
    public function findOneBy($property, $value, $comparator = '=');
}
