<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Bwrule extends \Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $table = 'bwrules';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'account_id',
        'user_id',
        'bwatch_id',
        'name',
        'bw_token',
        'project_id',
        'queries',
        'domain_id',
        'datamark',
        'fromdate',
        'action',
        'sentiment',
        'param1',
        'param2',
        'param3',
        'categories',
        'tags',
        'delete',
        'rule',
        'expires_in',
        'status',
        'is_active',
        'created_by',
        'updated_by',
        'created_by',
        'updated_by',
        'last_queue_time',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * A many-to-one relationship user to account
     *
     * @return mix
     */
    public function account() {
        return $this->belongsTo('Account');
    }

    /**
     * A one-to-one relationship user to users
     *
     * @return mix
     */
    public function user() {
        return $this->belongsTo('User');
    }

    /**
     * A one-to-one relationship bwrule to bwatch
     *
     * @return mix
     */
    public function bwatch() {
        return $this->belongsTo('Bwatch','bwatch_id');
    }
    
    /**
     * A one-to-one relationship bwrule to Bwstat
     *
     * @return mix
     */
    public function bwstats() {
        return $this->hasMany('BwruleStat','bwrule_id');
    }

     
}
