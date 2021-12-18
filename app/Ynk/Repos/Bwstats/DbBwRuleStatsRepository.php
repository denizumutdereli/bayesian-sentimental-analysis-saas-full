<?php 
namespace Ynk\Repos\Bwstats;

use Ynk\Repos\DbRepository;
use BwruleStat;

class DbBwruleStatsRepository extends DbRepository implements BwRuleStatsRepositoryInterface {

	protected $model;


	public function __construct(BwruleStat $model)
	{
		$this->model = $model;
	}
}