<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class BwruleStat extends \Eloquent {
	
    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $table = 'bwrulestats';
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
        'total_mentions',         
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    
    /**
     * A many-to-one relationship account
     *
     * @return mix
     */
    public function account() {
        return $this->belongsTo('Account');
    }

    /**
     * A one-to-one relationship user
     *
     * @return mix
     */
    public function user() {
        return $this->belongsTo('User');
    }

    /**
     * A one-to-one relationship Bwatch
     *
     * @return mix
     */
    public function bwatch() {
        return $this->belongsTo('Bwatch');
    }
    
    
    /**
     * A one-to-one relationship bwrule
     *
     * @return mix
     */
    public function bwrule() {
        return $this->belongsTo('Bwrule');
    }
    
}