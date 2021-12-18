<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class User extends Eloquent implements UserInterface, RemindableInterface {

    use SoftDeletingTrait;

    protected $softDelete = true;

    use UserTrait,
        RemindableTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password', 'remember_token');
    protected $fillable = [
        'account_id',
        'email',
        'password',
        'remember_token',
        'role',
        'permissions',
        'is_active',
        'created_by',
        'updated_by',
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
     * A one-to-one relationship user to pings
     *
     * @return mix
     */
    public function pings() {
        return $this->hasMany('Ping');
    }

    /**
     * A one-to-one relationship user to users
     *
     * @return mix
     */
    public function users() {
        return $this->hasMany('User', 'created_by');
    }

    /**
     * A one-to-one relationship user to sources
     *
     * @return mix
     */
    public function sources() {
        return $this->hasMany('Source');
    }

    /**
     * A one-to-one relationship user to uploads
     *
     * @return mix
     */
    public function uploads() {
        return $this->hasMany('Upload');
    }

    /**
     * A one-to-one relationship user to tags
     *
     * @return mix
     */
    public function tags() {
        return $this->hasMany('Tag');
    }

    /**
     * A one-to-one relationship user to TagUploads
     *
     * @return mix
     */
    public function taguploads() {
        return $this->hasMany('TagUpload');
    }

    /**
     * A one-to-one relationship user to domains
     *
     * @return mix
     */
    public function domains() {
        return $this->hasMany('Domain');
    }

    /**
     * A one-to-one relationship user to comments
     *
     * @return mix
     */
    public function comments() {
        return $this->hasMany('Comment');
    }

    /**
     * A one-to-one relationship user to sentimentals
     *
     * @return mix
     */
    public function sentimentals() {
        return $this->hasMany('Sentimental');
    }

    /**
     * A one-to-one relationship user to tickets
     *
     * @return mix
     */
    public function tickets() {
        return $this->hasMany('Ticket');
    }

    /**
     * A one-to-one relationship user to userLogs
     *
     * @return mix
     */
    public function userlogs() {
        return $this->hasMany('UserLog');
    }

    /**
     * A one-to-one relationship Bwatch
     *
     * @return mix
     */
    public function bwatchs() {
        return $this->hasMany('Bwatch');
    }
    
    
    /**
     * A one-to-one relationship Bwrule
     *
     * @return mix
     */
    public function bwrules() {
        return $this->hasMany('Bwrule');
    }
     
    /**
     * A one-to-one relationship Bwstat
     *
     * @return mix
     */
    public function bwstats() {
        return $this->hasMany('BwruleStat');
    }

    /**
     * User Role
     *
     * @return mix
     */
    public function is_superAdmin() {
        if ($this->getAttribute('role') == 'super')
            return true;
        else
            return false;
    }

    /**
     * User Role
     *
     * @return mix
     */
    public function is_Related($id) {
        $user = User::find($id);
        if ($this->getAttribute('account_id') == $user->account_id)
            return true;
        else
            return false;
    }

    public function isActive() {
        return $this->getAttribute('is_active');
    }

}
