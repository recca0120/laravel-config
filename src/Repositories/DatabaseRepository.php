<?php

namespace Recca0120\Config\Repositories;

use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Recca0120\Config\Config;

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
     * @param \Recca0120\Config\Config
     * @param \Illuminate\Contracts\Foundation\Application
     */
    public function __construct(RepositoryContract $config, Config $model, ApplicationContract $app)
    {
        parent::__construct($config, $app);
        $this->original = $config->all();
        $this->model = $model;

        $data = value(function () {
            $file = $this->getStorageFile();
            if (file_exists($file) === true) {
                return json_decode(file_get_contents($file), true);
            }
            $data = $this->getModel()->value;
            $this->store($data);

            return $data;
        });

        if (is_null($data) === false) {
            foreach (array_dot($data) as $key => $value) {
                $config->set($key, $value);
            }
        }
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
        parent::set($key, $value);
        $this->storeDiff();
    }

    /**
     * Unset a configuration option.
     *
     * @param string $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        parent::offsetUnset($key);
        $this->storeDiff();
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
     * store.
     *
     * @method store
     *
     * @param mix $data
     */
    protected function store($data)
    {
        file_put_contents($this->getStorageFile(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * storeDiff description.
     *
     * @method storeDiff
     */
    protected function storeDiff()
    {
        if ($this->needUpdate === false) {
            return;
        }
        $diff = $this->arrayDiffAssocRecursive($this->all(), $this->original);
        if (empty($diff) === false) {
            $model = $this->getModel();
            $model->fill(['value' => $diff])->save();
            $this->store($diff);
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
                if (! isset($array2[$key]) || ! is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->arrayDiffAssocRecursive($value, $array2[$key]);
                    if (! empty($new_diff)) {
                        $difference[$key] = $new_diff;
                    }
                }
            } elseif (! array_key_exists($key, $array2) || $array2[$key] !== $value) {
                $difference[$key] = $value;
            }
        }

        return $difference;
    }
}
