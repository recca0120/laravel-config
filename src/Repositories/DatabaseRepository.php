<?php

namespace Recca0120\Config\Repositories;

use Illuminate\Support\Arr;
use Recca0120\Config\Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Config\Repository;

class DatabaseRepository extends AbstractRepository
{
    /**
     * $original.
     *
     * @var array
     */
    protected $original = [];

    /**
     * $key.
     *
     * @var string
     */
    protected $key = 'configs';

    /**
     * $repository.
     *
     * @var \Recca0120\Config\Config
     */
    protected $model;

    /**
     * $filesystem.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * $config.
     *
     * @var array
     */
    protected $config;

    /**
     * __construct.
     *
     * @param \Illuminate\Contracts\Config\Repository $repository
     * @param \Recca0120\Config\Config $model
     * @param array $config
     */
    public function __construct(Repository $repository, Config $model, Filesystem $filesystem, $config = [])
    {
        parent::__construct($repository);
        $this->original = $repository->all();
        $this->model = $model;
        $this->filesystem = $filesystem;
        $this->config = $config;

        $data = (array) value(function () {
            $file = $this->getStorageFile();
            if ($this->filesystem->exists($file) === true) {
                return json_decode($this->filesystem->get($file), true);
            }
            $data = $this->getModel()->value;
            $this->storeToFile($data);

            return $data;
        });

        foreach (Arr::dot($data) as $key => $value) {
            $repository->set($key, $value);
        }
    }

    /**
     * Set a given configuration value.
     *
     * @param array|string $key
     * @param mixed $value
     */
    public function set($key, $value = null)
    {
        parent::set($key, $value);
        $this->store();
    }

    /**
     * Unset a configuration option.
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        parent::offsetUnset($key);
        $this->store();
    }

    /**
     * getStorageFile.
     *
     * @return string
     */
    public function getStorageFile()
    {
        return Arr::get($this->config, 'path').'config.json';
    }

    /**
     * cloneModel.
     *
     * @return \Recca0120\Config\Config
     */
    protected function cloneModel()
    {
        return clone $this->model;
    }

    /**
     * getModel.
     *
     * @return \Recca0120\Config\Config
     */
    protected function getModel()
    {
        return $this->cloneModel()->firstOrCreate([
            'key' => $this->key,
        ]);
    }

    /**
     * storeToFile.
     *
     * @param mixed $data
     * @return $this
     */
    protected function storeToFile($data)
    {
        if (is_null($data) === true) {
            $data = [];
        }
        $option = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        $this->filesystem->put(
            $this->getStorageFile(),
            json_encode($data, $option)
        );

        return $this;
    }

    /**
     * store.
     */
    protected function store()
    {
        $data = $this->protectedKeys(
            $this->arrayDiffAssocRecursive($this->all(), $this->original)
        );

        if (empty($data) === false) {
            $model = $this->getModel();
            $model
                ->fill(['value' => $data])
                ->save();
            $this->storeToFile($data);
        }
    }

    /**
     * arrayDiffAssocRecursive.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected function arrayDiffAssocRecursive($array1, $array2)
    {
        $difference = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (isset($array2[$key]) === false || is_array($array2[$key]) === false) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->arrayDiffAssocRecursive($value, $array2[$key]);
                    if (empty($new_diff) === false) {
                        $difference[$key] = $new_diff;
                    }
                }
            } elseif (array_key_exists($key, $array2) === false || $array2[$key] !== $value) {
                $difference[$key] = $value;
            }
        }

        return $difference;
    }

    /**
     * protectedKeys.
     *
     * @param array $data
     * @return array
     */
    protected function protectedKeys($data)
    {
        if (empty($keys = Arr::get($this->config, 'protected')) === false) {
            $data = Arr::except($data, $keys);
        }

        return $data;
    }
}
