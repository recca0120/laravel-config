# Laravel Config Extend
store config to database

## Installation

Add Presenter to your composer.json file:

```js
"require": {
    "recca0120/config": "~1.1.1"
}
```
Now, run a composer update on the command line from the root of your project:

    composer update

### Registering the Package

Include the service provider within `app/config/app.php`. The service povider is needed for the generator artisan command.

```php
'providers' => [
    ...
    Recca0120\Config\ServiceProvider::class,
    ...
];
```

Now publish the config file and migrations by running `php artisan vendor:publish`. The config file will give you control over which storage engine to use as well as some storage-specific settings.

_IMPORTANT_ if you are using the database driver don't forget to migrate the database by running `php artisan migrate`

## License

The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).
