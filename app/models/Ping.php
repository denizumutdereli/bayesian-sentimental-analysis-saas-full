<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Ping extends \Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $table = 'pings';
    protected $fillable = [
        'account_id',
        'amount',
        'section',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * A many-to-one relationship pings to account
     *
     * @return mix
     */
    public function account() {
        return $this->belongsTo('Account');
    }

    /**
     * A many-to-one relationship pings to user
     *
     * @return mix
     */
    public function user() {
        return $this->belongsTo('User');
    }

}
