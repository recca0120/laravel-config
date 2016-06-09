<?php

namespace Recca0120\Config\Repositories;

use Illuminate\Contracts\Config\Repository as RepositoryContract;

/**
 *  DatabaseRepository.
 */
class DatabaseRepository extends AbstractRepository
{
    /**
     * $repository.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $repository;

    /**
     * $needUpdate.
     *
     * @var [type]
     */
    protected $needUpdate = true;

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param ConfigRepository                        $repository
     */
    public function __construct(RepositoryContract $config, ConfigRepository $repository)
    {
        parent::__construct($config);
        $this->repository = $repository;
        $this->config->set($this->repository->all());
    }

    /**
     * Set a given configuration value.
     *
     * @param array|string $key
     * @param mixed        $value
     *
     * @return void
     */
    public function set($key, $value = null)
    {
        $this->config->set($key, $value);
        if ($this->needUpdate === true) {
            $this->repository->store($this->config->all());
        }
    }

    /**
     * needUpdate.
     *
     * @method needUpdate
     *
     * @param bool $status
     *
     * @return void
     */
    public function needUpdate($status)
    {
        $this->needUpdate = $status;
    }
}
