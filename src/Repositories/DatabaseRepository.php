<?php

namespace Recca0120\Config\Repositories;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Foundation\Application;
use Recca0120\Config\Config;
use Illuminate\Support\Arr;

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
     * $filesystem
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * $config
     *
     * @var string
     */
    protected $config;

    /**
     * $needUpdate.
     *
     * @var bool
     */
    protected $needUpdate = true;

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Illuminate\Contracts\Config\Repository   $repository
     * @param \Recca0120\Config\Config                  $model
     * @param string                                    $config
     */
    public function __construct(Repository $repository, Config $model, Filesystem $filesystem, $config = [])
    {
        parent::__construct($repository);
        $this->original = $repository->all();
        $this->model = $model;
        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->load();
    }

    protected function load() {
        $data = value(function () {
            $file = $this->getStorageFile();
            if ($this->filesystem->exists($file) === true) {
                return json_decode($this->filesystem->get($file), true);
            }
            $data = $this->getModel()->value;
            $this->storeToFile($data);

            return $data;
        });

        foreach (array_dot($data) as $key => $value) {
            $repository->set($key, $value);
        }

        return $this;
    }

    /**
     * Set a given configuration value.
     *
     * @param array|string $key
     * @param mixed        $value
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
     * needUpdate.
     *
     * @method needUpdate
     *
     * @param bool $status
     *
     * @return self
     */
    public function needUpdate($status)
    {
        $this->needUpdate = $status;

        return $this;
    }

    /**
     * cloneModel.
     *
     * @method cloneModel
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
     * @method getModel
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
     * @method storeToFile
     *
     * @param mix $data
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
     *
     * @method store
     */
    protected function store()
    {
        if ($this->needUpdate === false) {
            return;
        }

        $diff = $this->arrayDiffAssocRecursive($this->all(), $this->original);
        if (empty($diff) === false) {
            $model = $this->getModel();
            $model
                ->fill(['value' => $diff])
                ->save();
            $this->storeToFile($diff);
        }
    }

    /**
     * arrayDiffAssocRecursive.
     *
     * @method arrayDiffAssocRecursive
     *
     * @param array $array1
     * @param array $array2
     *
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
     * getStorageFile.
     *
     * @method getStorageFile
     *
     * @return string
     */
    public function getStorageFile()
    {
        return Arr::get($this->config, 'storagePath').'config.json';
    }
}
