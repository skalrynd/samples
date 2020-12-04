<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Models\OrderDetails;
use \App\Models\Inventory;
use App\Models\PaymentMethod;
use App\Models\ShoppingCart;
use App\Models\Note;
use \App\Models\ShippingMethod;
use Carbon\Carbon;
use DB;
use Auth;
use Log;
use FormatHelper;

class Order extends Model
{
	protected $connection   = 'se';
	protected $table        = 'Orders';
    	protected $primaryKey   = 'OrderNumber';
    	public    $incrementing = false;
	public    $timestamps   = false;	
	
	protected $hidden = [
		'TimeStamp',
		'ShipOn',  // not used
		'OrderInst', // not used
		'NumItems',  // all 0
		'...',  // all NULL		
	];
	
	protected $fillable = [
		'OrderNumber',
		'OrderSource',   // XC or MO
		'SourceOrderNumber', // XC number
		'OrderDate',  // date order was placed
		'OrderTime', // time order was placed
		'CustomerID', // id of customer
		'...', 	
 	];
	 
	
	public function order_details()
	{	
		return $this->hasMany(OrderDetails::Class, 'OrderNumber', 'OrderNumber');
	}
	
	public function notes()
	{
		return $this->hasMany(Note::Class, 'NumericKey', 'OrderNumber');
	}
    
    	public function paymentMethod()
    	{
        	return $this->hasOne(PaymentMethod::class, 'ID', 'PayType');
    	}
 
    	public function shoppingCart()
    	{
    	    	return $this->hasOne(ShoppingCart::class, 'ID', 'CartID');
    	}
	
	public function source()
	{
		return $this->hasOne(ShoppingCart::Class, 'ID', 'CartID');
	}

} 
