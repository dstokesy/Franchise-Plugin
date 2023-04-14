<?php namespace Dstokesy\Franchises\Models;

use Model;
use Config;
use Dstokesy\Franchises\Models\Info;

/**
 * Franchise Model
 */
class Franchise extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dstokesy_franchises';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array validation rules
     */
    public $rules = [
    	'name'	=> 'required',
    	'slug'  => ['required', 'between:1,191', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:dstokesy_franchises,slug,NULL,id,deleted_at,NULL'],
    ];

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
    public $hasOne = [
    	'info'	=> [
    		'Dstokesy\Franchises\Models\Info',
    		'delete'	=> true
    	]
    ];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function afterCreate()
    {
    	$info = new Info();
    	$info->franchise_id = $this->id;
    	$info->save();
    }

    public function scopeIsLive($query)
    {
        return $query->where('is_live', 1);
    }

    public function getHostAttribute()
    {
    	$scheme = parse_url(Config::get('app.url'), PHP_URL_SCHEME);
    	$host = parse_url(Config::get('app.url'), PHP_URL_HOST);

    	if ($this->domain) {
    		$host = $this->domain;
    	} else {
    		$host = str_replace('www', $this->slug, $host);
    	}

    	$host = $scheme . '://' . $host;

    	return $host;
    }
}
