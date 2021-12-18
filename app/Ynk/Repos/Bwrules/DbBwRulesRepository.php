<?php 
namespace Ynk\Repos\Bwrules;

use Ynk\Repos\DbRepository;
use Bwrule;

class DbBwrulesRepository extends DbRepository implements BwRulesRepositoryInterface {

	protected $model;


	public function __construct(Bwrule $model)
	{
		$this->model = $model;
	}
}