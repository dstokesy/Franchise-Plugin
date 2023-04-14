<?php namespace Dstokesy\Franchises\Behaviors;

use App;
use Event;
use Schema;
use Illuminate\Database\Eloquent\Scope;
use Dstokesy\Franchises\Classes\Franchiser;
use Backend\Models\User as BackendUser;

class FranchisableScope implements Scope
{
	/**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
	public function apply(\Illuminate\Database\Eloquent\Builder $builder, \Illuminate\Database\Eloquent\Model $model)
	{
		$franchiser = Franchiser::instance();
		$franchise = $franchiser->getFranchise();
		$table = $builder->getQuery()->from;

		if (str_contains($table, 'laravel_reserved')) {
			$tableParts = explode(' as', $table);
			if (isset($tableParts[0])) {
				$table = $tableParts[0];
			}
		}

		if ($franchise) {
			if (!Schema::hasColumn($table, 'is_global')) {
				$builder = $this->getFranchiseQuery($builder, $model, $table, $franchise);
			} else if (!App::runningInBackend()) {
				$builder->where(function($q) use($franchise, $table) {
					return $q->where($table . '.franchise_id', $franchise->id)
						->orWhere(function($q2) use($table) {
							$q2->whereNull($table . '.franchise_id')
								->where('is_global', 1);
						});
				});
			} else {
				$builder = $this->getFranchiseQuery($builder, $model, $table, $franchise);
			}
		} else {
			$builder->whereNull($table . '.franchise_id');
		}
	}

	public function getFranchiseQuery($builder, $model, $table, $franchise)
	{
		if ($model instanceof BackendUser) {
			$builder->where($table . '.franchise_id', $franchise->id)
				->orWhereNull($table . '.franchise_id');
		} else {
			$result = Event::fire('dstokesy.franchises.franchisableScope.beforeCreateFranchiseQuery', [$this, &$builder, $model], true);

			if (!$result) {
				$builder->where($table . '.franchise_id', $franchise->id);
			}
		}

		return $builder;
	}
}
