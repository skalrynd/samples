<?php
namespace App\Models;

use DB;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

// holds data on the items on each order
class OrderDetails extends Model
{
	use \App\Traits\HasCompositePrimaryKey; //custom for composite primary keys
	
	protected $connection = 'se';
	protected $table      = 'Order Details';
	public $timestamps    = false;
	protected $primaryKey = array('OrderNumber', 'ItemNumber');

	
	protected $fillable = [
		'OrderNumber',
		'ItemNumber',
		'DetailDate',
		'Adjustment', // markes items that change money IE shipping charge, coupons, discounts, etc.
		'...'
	];
	
	protected $hidden = [
			'TimeStamp', // SQL Server thing
			'Finite_options', //??? not used
			'Freeform_Options', //???? not used
			'...', // etc
	];
	
	public function parent() {
		return $this->belongsTo(Order::class, 'OrderNumber', 'OrderNumber');
	}

	public function getSkuAttribute($value)
	{
		return iconv('ISO-8859-1', 'UTF-8', trim($value));
	}
	
	public function getProductAttribute($value)
	{
		return iconv('ISO-8859-1', 'UTF-8', trim($value));
	}

	
	/**
	* SCOPE : get backordered (order details)
	* ->backorders()
	*/
	public function scopeBackorders($query) {
		return $query->where('QuantityNeeded', '!=', '0');
	}
  
	public function scopeAdjustedItemsAvailable($query) 
	{
		$query->addSelect('Order Details.OrderNumber',
		DB::raw('(CASE WHEN [DateShipped] IS NOT NULL THEN 0 ELSE ISNULL([QuantityShipped], 0) END) 
			+ (CASE WHEN [DateShipped] IS NOT NULL OR
			  (CASE WHEN [DateShipped] IS NOT NULL THEN 0 ELSE ISNULL([QuantityNeeded], 0) END) <= (CASE WHEN [DateShipped] IS NOT NULL 
			  THEN 0 ELSE ISNULL([QuantityShipped], 0) END) THEN 0 ELSE (CASE WHEN [DateShipped] IS NOT NULL THEN 0 ELSE ISNULL([QuantityNeeded], 0) END) 
			- (CASE WHEN [DateShipped] IS NOT NULL THEN 0 ELSE ISNULL([QuantityShipped], 0) END) END) 
			AS NetQtyAvailForPREAdj'),
		DB::raw('(CASE WHEN [DateShipped] IS NULL THEN 0 ELSE ISNULL([QuantityShipped], 0) END)
			AS NetQtyAvailForPOSTAdj'));
		return $query;
	}
  
	/**
	* SCOPE: some general default filtering for item adjustments (exclude warranty, Details adjustment, etc) 
	* 
	* @param type $query
	*/
	public function scopeAdjustmentFilter($query)
	{
		$query->where('Order Details.SKU', 'NOT LIKE', '%WARRANTY%')
	    ->where('Order Details.Adjustment', '=', 0);
	}
}
