<?php
namespace Ynk\Api\Tweets;


use Laracasts\Validation\FormValidator;

class ApiTweetValidation extends FormValidator {

    protected $rules = [
        'name' => 'required'
    ];
} 