<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class UserLog extends \Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $table = 'user_logs';
    protected $fillable = [
        'account_id',
        'user_id',
        'source',
        'log',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * A many-to-one relationship userlog to account
     *
     * @return mix
     */
    public function account() {
        return $this->belongsTo('Account');
    }

    /**
     * A many-to-one relationship userlog to account
     *
     * @return mix
     */
    public function user() {
        return $this->belongsTo('User');
    }

    public function getLogAttribute($log) {
        return json_decode($log);
    }

}
