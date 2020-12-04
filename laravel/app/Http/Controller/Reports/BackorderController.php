<?php
namespace App\Http\Controllers\Utilities\Reports;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\POHistory;

class BackorderController extends Controller implements ReportInterface {
    
    private static function _getFullQb() {
        $qb = Order::select('Orders.OrderNumber', 'Orders.SourceOrderNumber', 
                'od.QuantityNeeded', 'od.SKU', 'od.PricePerUnit', 
                'Inventory.ItemName', 'od.Adjustment', 'Orders.Name', 
                'Orders.OrderDate', 'Orders.Cancelled', 'od.DropShip', 
                'Inventory.QOH', 'Orders.ShipState', 'Orders.SourceOrderID', 
                'Inventory.Message', 'od.Text1', DB::raw('[QuantityNeeded]-[QOH] AS INV'), 
                'od.ExpectedShipDate', 'Inventory.Kit', 'od.ItemNumber')
                ->join(DB::raw('[Order Details] AS od'), 'od.OrderNumber', '=', 'Orders.OrderNumber')
                ->join('Inventory', 'Inventory.LocalSKU', '=', 'od.SKU')
                ->where('od.QuantityNeeded', '>', 0)
                ->where('od.Adjustment', '=', 0)
                ->where('Orders.Cancelled', '=', 0)
                ->where('od.DropShip', '=', 0)
                ->orderBy('od.SKU');
        return $qb;
    }
    
    public static function params(Request $request) {
        return response()->view('pages/utilities/reports/backorder-params');
    }
    
    public static function render(Request $request) {
        $params = [];
        $tomorrow = time() + 86400;
        $qb = self::_getFullQb();
        if (preg_match('/^full/', $request->input('view'))) {
            $qb->where('Inventory.Kit', '=', 0);
            $poqb = POHistory::select('LocalSKU', DB::raw('SUM(Quantity) AS Expected'))
                    ->where('Type', '=', 'E')
                    ->groupBy('LocalSKU');
            $pos = $poqb->get()->groupBy('LocalSKU')->toArray();
            $items = $qb->get();
            foreach($items as $item) {
                $item->Expected = isset($pos[$item->SKU]) ? $pos[$item->SKU][0]['Expected'] : NULL;
            }
            if ($request->input('view') == 'full-email-sent') {
                $qb->whereNull('od.Text5');
            }
        } elseif ($request->input('view') == 'no-exp') {
            $qb->where(DB::raw('[QuantityNeeded]-[QOH]'), '>', 0)
               ->where('od.ExpectedShipDate', '<', DB::raw('GETDATE()+1'));
            $items = $qb->get();
            foreach($items as $i=>$item) {  //Filter by BackorderDetails - No Expected Date
                $sidQb = OrderDetails::select('od.Text1 AS ShipsInDate')
                        ->from(DB::raw('[Order Details] AS od'))
                        ->join('Orders', 'od.OrderNumber', '=', 'Orders.OrderNumber')
                        ->join('Inventory', 'Inventory.LocalSKU', '=', 'od.SKU')
                        ->where('od.QuantityNeeded', '>', 0)
                        ->where('od.Adjustment', '=', 0)
                        ->where('Orders.Cancelled', '=', 0)
                        ->where('od.DropShip', '=', 0)
                        ->where('od.ExpectedShipDate', '<', DB::raw('GETDATE() + 1'))
                        ->where('od.ItemNumber', '=', $item->ItemNumber)
                        ->where('od.OrderNumber', '=', $item->OrderNumber);
                $res = $sidQb->get();
                if (strtotime($res->first()->ShipsInDate) >= $tomorrow) {
                    $items->forget($i);
                }
            }
        }
        
        $params['items'] = $items;
        return response()->view('pages/utilities/reports/backorder', $params);
    }
}
