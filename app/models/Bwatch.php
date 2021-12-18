<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Bwatch extends \Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $table = 'bwatchs';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'account_id',
        'user_id',
        'username',
        'bw_token',
        'expires_in',
        'status',
        'is_active',
        'token_type',
        'scope',
        'json',
        'created_by',
        'updated_by',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    protected static $settings = null;

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
    public function users() {
        return $this->belongsTo('User');
    }

    /**
     * A one-to-one relationship bwaccounts_to bwrules
     *
     * @return mix
     */
    public function bwrules() {
        return $this->hasMany('Bwrule', 'bwatch_id');
    }

    /**
     * A one-to-one relationship bwaccounts_to bwtats
     *
     * @return mix
     */
    public function bwstats() {
        return $this->hasMany('BwruleStat', 'bwatch_id');
    }

    /**
     * Engagement Status
     *
     * @return boolen
     */
    public function is_connected() {
        if ($this->getAttribute('status') == '1')
            return true;
        else
            return false;
    }

    /**
     * Activation Status
     *
     * @return boolen
     */
    public function is_active() {
        if ($this->getAttribute('is_active') == '1')
            return true;
        else
            return false;
    }

    /**
     * Get Settings
     *
     * @return boolen
     */
    public function getSettings() {
        self::$settings = json_decode($this->getAttribute('json'), true);
        return self::$settings;
    }

    /**
     * Get BW Account Details
     *
     * @return boolen
     */
    public function getBWAccount() {
        if ($this->settings) {
            return self::getSettings()["links"]['client'];
        }
        return array();
    }

    /**
     * Get BW Account Project Details
     *
     * @return boolen
     */
    public function getProjects() {
        if ($this->settings) {
            return self::getSettings()['links']['projects'];
        }
        return array();
    }

}
