<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Invoice extends \Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $table = 'invoices';
    protected $fillable = [
        'user_id',
        'account_id',
        'amount',
        'details',
        'status',
        'payment_type',
        'payment_date',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * A many-to-one relationship domain to account
     *
     * @return mix
     */
    public function account() {
        return $this->belongsTo('Account');
    }

}
