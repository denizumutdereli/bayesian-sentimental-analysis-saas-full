<?php namespace Ynk\Repos\Upload;

use Ynk\Repos\DbRepository;
use Upload;

class DbUploadRepository extends DbRepository implements UploadRepositoryInterface {

	protected $model;


	public function __construct(Upload $model)
	{
		$this->model = $model;
	}
}