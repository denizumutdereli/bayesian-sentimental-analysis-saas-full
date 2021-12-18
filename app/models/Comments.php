<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Comments extends Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $fillable = [
        'account_id',
        'user_id',
        'source_id',
        'file_id',
        'post_id',
        'post_title',
        'post_url',
        'post_type',
        'comment_id',
        'text',
        'username',
        'url',
        'is_published',
        'is_processed',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * A many-to-one relationship comments to account
     *
     * @return mix
     */
    public function account() {
        return $this->belongsTo('Account');
    }

    /**
     * A many-to-one relationship comments to user
     *
     * @return mix
     */
    public function user() {
        return $this->belongsTo('User');
    }

    /**
     * A many-to-one relationship comments to user
     *
     * @return mix
     */
    public function source() {
        return $this->belongsTo('Source');
    }

}
