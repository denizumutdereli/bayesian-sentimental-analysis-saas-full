<?php namespace Ynk\Repos\Tagupload;

use Ynk\Repos\DbRepository;
use TagUpload;

class DbTagUploadRepository extends DbRepository implements TagUploadRepositoryInterface {

	protected $model;


	public function __construct(TagUpload $model)
	{
		$this->model = $model;
	}
}