<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Ngram extends \Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $table = 'ngram_positive';
    public $timestamps = false;
    protected $fillable = [];

}
