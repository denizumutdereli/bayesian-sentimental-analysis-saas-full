<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class News extends \Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $fillable = ['origin_id', 'title', 'news_url', 'total_comments', 'unreaded_comments'];

}
