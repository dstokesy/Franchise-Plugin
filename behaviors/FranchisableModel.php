<?php namespace Dstokesy\Franchises\Behaviors;

use Schema;
use October\Rain\Extension\ExtensionBase;
use Dstokesy\Franchises\Behaviors\FranchisableScope;
use Dstokesy\Franchises\Classes\Franchiser;
use ValidationException;

class FranchisableModel extends ExtensionBase
{
	/**
     * @var \October\Rain\Database\Model Reference to the extended model.
     */
    protected $model;

    /**
     * Constructor
     * @param \October\Rain\Database\Model $model The extended model.
     */
    public function __construct($model)
    {
        $this->model = $model;

        $this->model->addGlobalScope(new FranchisableScope);

        // if validating slug as unique check that it is unique in this franchise only
        $Franchiser = Franchiser::instance();
        $franchise = $Franchiser->getFranchise();

        $this->model->belongsTo['franchise'] = [
            'Dstokesy\Franchises\Models\Franchise',
            'key'	=> 'franchise_id'
        ];

        // Only validate unique slugs against other slugs in the franchise
        $this->model->bindEvent('model.beforeValidate', function() {
        	if (isset($this->model->rules['slug'])) {
        		$this->runSlugValidation();
        	}
        });

		// set franchise id when model is first saved
		$this->model->bindEvent('model.beforeCreate', function() use($franchise) {
			if ($this->model->force_franchise_id) {
				$franchiseId = $this->model->force_franchise_id;
				unset($this->model->force_franchise_id);
			} else {
				$franchiseId = ($franchise ? $franchise->id : null);
			}
			$this->model->franchise_id = $franchiseId;

		});
    }

    private function runSlugValidation()
    {
        $class = get_class($this->model);

        $this->model->rules['slug'] = [
        	'required',
        	'between:1,191',
        	'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i'
        ];

        $query = $class::where('slug', $this->model->slug);

        if ($this->model->id) {
            $query->where('id','!=', $this->model->id);
        }

        if (Schema::hasColumn($this->model->table, 'deleted_at')) {
        	$query->whereNull('deleted_at');
        }

		$exists = $query->exists();

        if ($exists) {
            throw new ValidationException(['slug' => 'The slug is already taken']);
        }

        return $this;
    }
}
