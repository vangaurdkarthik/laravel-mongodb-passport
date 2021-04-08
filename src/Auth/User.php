<?php

namespace MongoPassport\Mongodb\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model;
use Laravel\Passport\HasApiTokens;

class User extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
}
