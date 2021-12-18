<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Tag extends Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $fillable = [
        'user_id',
        'account_id',
        'name',
        'about',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * A many-to-one relationship tag to account
     *
     * @return mix
     */
    public function account() {
        return $this->belongsTo('Account');
    }

    /**
     * A many-to-one relationship tag to user
     *
     * @return mix
     */
    public function user() {
        return $this->belongsTo('User');
    }

    /**
     * A many-to-one relationship uploads to tag
     *
     * @return mix
     */
    public function uploads() {
        return $this->hasMany('TagUpload');
    }

}
