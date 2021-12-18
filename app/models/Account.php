<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Account extends \Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $table = 'accounts';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'accountType',
        'package',
        'api',
        'api_key',
        'api_secret',
        'access_token',
        'name',
        'about',
        'logo',
        'is_active',
        'created_at',
        'is_active',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * A one-to-one relationship account to pings
     *
     * @return mix
     */
    public function pings() {
        return $this->hasMany('Ping');
    }

    /**
     * A one-to-one relationship account to users
     *
     * @return mix
     */
    public function users() {
        return $this->hasMany('User');
    }

    /**
     * A one-to-one relationship account to invoinces
     *
     * @return mix
     */
    public function invoices() {
        return $this->hasMany('Invoice');
    }

    /**
     * A one-to-one relationship account to sources
     *
     * @return mix
     */
    public function sources() {
        return $this->hasMany('Source','account_id');
    }

    /**
     * A one-to-one relationship account to uploads
     *
     * @return mix
     */
    public function uploads() {
        return $this->hasMany('Upload');
    }

    /**
     * A one-to-one relationship account to tags
     *
     * @return mix
     */
    public function tags() {
        return $this->hasMany('Tag');
    }

    /**
     * A one-to-one relationship account to TagUploads
     *
     * @return mix
     */
    public function taguploads() {
        return $this->hasMany('TagUpload');
    }

    /**
     * A one-to-one relationship account to domains
     *
     * @return mix
     */
    public function domains() {
        return $this->hasMany('Domain');
    }

    /**
     * A one-to-one relationship account to comments
     *
     * @return mix
     */
    public function comments() {
        return $this->hasMany('Comments');
    }

    /**
     * A one-to-one relationship account to sentimentals
     *
     * @return mix
     */
    public function sentimentals() {
        return $this->hasMany('Sentimental');
    }

    /**
     * A one-to-one relationship account to tickets
     *
     * @return mix
     */
    public function tickets() {
        return $this->hasMany('Ticket');
    }

    /**
     * A one-to-one relationship account to userLogs
     *
     * @return mix
     */
    public function userlogs() {
        return $this->hasMany('UserLog');
    }

    
    /**
     * A one-to-one relationship account to bwatch accounts
     *
     * @return mix
     */
    public function bwatchs() {
        return $this->hasMany('Bwatch');
    }
    
    
    /**
     * A one-to-one relationship  bwrules
     *
     * @return mix
     */
    public function bwrules() {
        return $this->hasMany('Bwrule');
    }
     
    /**
     * A one-to-one relationship bwtats
     *
     * @return mix
     */
    public function bwstats() {
        return $this->hasMany('BwruleStat');
    }
  

}
