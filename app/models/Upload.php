<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Upload extends Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $fillable = [
        'name',
        'account_id',
        'user_id',
        'source_id',
        'count',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * A one-to-one relationship upload to account
     *
     * @return mix
     */
    public function account() {
        return $this->belongsTo('Account');
    }

    /**
     * A one-to-one relationship upload to user
     *
     * @return mix
     */
    public function user() {
        return $this->belongsTo('User');
    }

    /**
     * A one-to-one relationship upload to sources
     *
     * @return mix
     */
    public function source() {
        return $this->belongsTo('Source');
    }

    /**
     * A many-to-one relationship comments to uploads
     *
     * @return mix
     */
    public function comments() {
        return $this->belongsTo('Comments', 'file_id');
    }

}
