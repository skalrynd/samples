<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class POHistory extends Model
{
	protected $connection = 'se';
	protected $table      = 'POHistory';
	protected $primaryKey = 'AutoNumber';
	public $timestamps    = false;
	public $incrementing  = false;

	public function po() {
		return $this->belongsTo('App\Models\PurchaseOrders', 'PONumber', 'PONumber');
	}

	public function inventory() {
		return $this->belongsTo('App\Models\Inventory2', 'LocalSKU', 'LocalSKU');
	}

	public function details() {
	// return $this->hasOne('App\Models\PurchaseOrderDetails')->with('PONumber','PONumber')->with('LocalSKU','LocalSKU');
	}



	/**
	* Active: NOT "adjustment" A
	* meaning, E or R
	*/
	public function scopeActive($query) {
		return $query->where('type', '!=', 'A');
	}
}
