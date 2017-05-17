<?php

require_once __DIR__.'/bootstrap.php';

use Doctrine\DBAL\DriverManager;
use Adagio\DataStore\Adapter\DbalStore;
use Adagio\Repository\Repository;

$conn = DriverManager::getConnection(['url' => 'pgsql://repo:repo@localhost/repo']);
$repository = new Repository(new DbalStore($conn));

class Foo
{
    private $bar;

    public function __construct($bar)
    {
        $this->bar = $bar;
    }

    public function bar()
    {
        return $this->bar;
    }
}

class Baz
{
    private $id;
    private $bar;

    public function __construct($id, $bar)
    {
        $this->id = $id;
        $this->bar = $bar;
    }

    public function id()
    {
        return $this->id;
    }

    public function bar()
    {
        return $this->bar;
    }
}

class Bar
{
    private $id;
    private $baz;

    public function __construct($id, $baz)
    {
        $this->id = $id;
        $this->baz = $baz;
    }

    public function id()
    {
        return $this->id;
    }

    public function baz()
    {
        return $this->baz;
    }
}

var_dump($k1 = $repository->store(new Foo('yo')));
var_dump($repository->store(new Baz($k2 = 'id_'.uniqid(), 'yo')));
var_dump($repository->get($k1));
var_dump($e2 = $repository->get($k2));
var_dump($repository->has($k1));
var_dump($repository->findOneBy('bar', 'yo'));
var_dump($repository->findAll());
$repository->remove($k1);
$repository->remove($e2);
var_dump($repository->has($k1));
