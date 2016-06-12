<?php

namespace Recca0120\Config\Repositories;

use Recca0120\Config\Config;

/**
 * ConfigRepository.
 */
class ConfigRepository
{
    /**
     * $model.
     *
     * @var \Recca0120\Config\Config
     */
    protected $model;

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Recca0120\Config\Config $model
     */
    public function __construct(Config $model)
    {
        $this->model = $model;
        $this->model->observe($this);
    }

    /**
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    public function all()
    {
        $path = $this->getStoragePath();
        if (file_exists($path) === true) {
            return json_decode(file_get_contents($path), true);
        }

        $data = value(function () {
            $model = $this->cloneModel()->find(1);
            if (is_null($model) === false) {
                return $model->value;
            }

            return [];
        });

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $data;
    }

    /**
     * store.
     *
     * @method store
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function store($data)
    {
        $model = $this->cloneModel()
            ->firstOrCreate([
                'id' => 1,
            ]);

        $model->fill([
            'value' => $data,
        ])->save();

        return $model;
    }

    /**
     * cloneModel.
     *
     * @method cloneModel
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function cloneModel()
    {
        return clone $this->model;
    }

    /**
     * saved observe.
     *
     * @method saved
     *
     * @return void
     */
    public function saved()
    {
        @unlink($this->getStoragePath());
    }

    /**
     * getStoragePath.
     *
     * @method getStoragePath
     *
     * @return string
     */
    public function getStoragePath()
    {
        return storage_path('config.json');
    }
}
