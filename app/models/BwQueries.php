<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class BwQueries extends \Eloquent {
	
    use SoftDeletingTrait;
  
    protected $softDelete = true;
    protected $table = 'bwqueries';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'account_id',
        'bwatch_id',
        'project_id',
        'queryId',
        'name',
        'cycle',
        'error',
        'errorCode',
        'errorMessage',
        'request',
        'resultsPage',
        'resultsPageSize',
        'resultsTotal',
        'pulledData',
        'startDate',
        'endDate',
        'maximumIdInResult',
        'maximumId',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
     
   
    public function isActive() {
        return $this->getAttribute('is_active');
    }
  
}