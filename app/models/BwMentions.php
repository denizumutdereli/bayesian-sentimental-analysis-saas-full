<?php

class BwMentions extends \Eloquent {

    use SoftDeletingTrait;

    protected $connection = 'bw_mysql';
    protected $table = 'mentions';
    protected $fillable = [
        'resourceId',
        'queryId',
        'queryName',
        'mention',
        'added',
        'pageType',
        'domain',
        'author',
        'mediaUrls',
        'url',
        'json',
        'created_at',
        'updated_at'
    ];

}
