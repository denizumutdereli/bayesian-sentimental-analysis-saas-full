<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Ticket extends \Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $table = 'tickets';
    protected $fillable = [
        'account_id',
        'user_id',
        'parent_id',
        'title',
        'description',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * A many-to-one relationship ticket to account
     *
     * @return mix
     */
    public function account() {
        return $this->belongsTo('Account');
    }

    /**
     * A many-to-one relationship ticket to user
     *
     * @return mix
     */
    public function user() {
        return $this->belongsTo('User');
    }

    /**
     * A many-to-one relationship ticket to replies
     *
     * @return mix
     */
    public function replies() {
        return $this->hasMany('Ticket', 'parent_id');
    }

    public function scopeParent($query) {
        return $query->whereParentId(0);
    }

    public function scopeActive($query) {
        return $query->whereStatus(1);
    }

}
