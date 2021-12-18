<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class TagUpload extends Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $fillable = [
        'account_id',
        'user_id',
        'tag_id',
        'file_id',
        'tag',
        'type',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * A many-to-one relationship tagupload to account
     *
     * @return mix
     */
    public function account() {
        return $this->belongsTo('Account');
    }

    /**
     * A many-to-one relationship tagupload to user
     *
     * @return mix
     */
    public function user() {
        return $this->belongsTo('User');
    }

    /**
     * A many-to-one relationship tagupload to tag
     *
     * @return mix
     */
    public function tag() {
        return $this->belongsTo('Tag');
    }

}
