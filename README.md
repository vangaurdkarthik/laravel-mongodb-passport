Laravel 8 MongoDB Passport
===============
A service provider to add support for [Laravel 8 Passport](https://github.com/laravel/passport) and [MongoDB](https://github.com/jenssegers/laravel-mongodb).

Table of contents
-----------------
* [Installation](#installation)

Installation
------------

Installation using composer:

```sh
composer require vangaurd/mongodb-passport
```
You need to have your `App\User` class extend `MongoPassport\Mongodb\Auth\User.php` instead of the default `Illuminate\Foundation\Auth\User`. This user class extends larvel-mongodb eloquent user as well as adding all the standard and required authentication and laravel passport traits.


```php
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use MongoPassport\Mongodb\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
}
```

And add the service provider in `config/app.php`:

```php
MongoPassport\Mongodb\MongodbPassportServiceProvider::class,