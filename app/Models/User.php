<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;


class User extends Model
{
  protected $fillable=['firstname','lastname','email','mobile','password','otp'];
  protected $attributes=[
      'otp'=>'0'
  ];
}
