<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Sentimental extends \Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $table = 'sentimental';
    protected $fillable = [
        'user_id',
        'account_id',
        'domain_id',
        'text',
        'state',
        'source',
        'source_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * A many-to-one relationship sentimental to account
     *
     * @return mix
     */
    public function account() {
        return $this->belongsTo('Account');
    }

    /**
     * A many-to-one relationship sentimental to user
     *
     * @return mix
     */
    public function user() {
        return $this->belongsTo('User');
    }

    /**
     * A many-to-one relationship sentimental to domain
     *
     * @return mix
     */
    public function domain() {
        return $this->belongsTo('Domain');
    }

}
