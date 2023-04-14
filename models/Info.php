<?php namespace Dstokesy\Franchises\Models;

use Model;
use Cache;

/**
 * Info Model
 */
class Info extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dstokesy_franchises_info';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
	 * @var array Behaviours Implemented
	 */
	public $implement = [
		'@RainLab.Translate.Behaviors.TranslatableModel',
		'@Dstokesy.Translate.Behaviors.TranslatableModel',
	];

	/**
	 * @var array Translated fields
	 */
	public $translatable = [];

    /**
     * @var array validation rules
     */
    public $rules = [];

    /**
     * @var array Fillable fields
     */
    public $fillable = [];

    /**
     * @var array JSON fields
     */
    public $jsonable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
    	'franchise'	=> [
    		'Dstokesy\Franchises\Models\Franchise'
    	]
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function beforeUpdate() {
    	Cache::forget('rainlab.translate.locales.franchise_' . $this->id);
    }
}
