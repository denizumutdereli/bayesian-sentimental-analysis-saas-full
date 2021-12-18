<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Source extends Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $fillable = [
        'name',
        'account_id',
        'user_id',
        'about',
        'is_published',
        'is_processed',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * A many-to-one relationship source to account
     *
     * @return mix
     */
    public function account() {
        return $this->belongsTo('Account');
    }

    /**
     * A many-to-one relationship source to user
     *
     * @return mix
     */
    public function user() {
        return $this->belongsTo('User');
    }

    /**
     * A many-to-one relationship uploads to source
     *
     * @return mix
     */
    public function uploads() {
        return $this->hasMany('Upload');
    }
    
    /**
     * A many-to-one relationship comments to source
     *
     * @return mix
     */
    public function comments() {
        return $this->hasMany('Comments');
    }

}
