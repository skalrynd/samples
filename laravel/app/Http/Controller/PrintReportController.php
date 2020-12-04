<?php
namespace App\Http\Controllers\Utilities;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilities\Reports\ReportInterface;

class PrintReportController extends Controller {
    
    private static function _report_fraudOrders(Request $request) {
        
    }
    
    /**
     * @function index
     * Front page return for print reports.
     * 
     * @param Request $request
     */
    public function index(Request $request) {
        $params = [];
        return response()->view('pages/utilities/print-report', $params);
    }
    
    /**
     * @function params
     * Returns any possible custom form parameters that should be delivered with 
     * the requested report.
     * 
     * This is mainly a placeholder for now as there  are no custom parameters
     * currently requested for pre-print reports.  But if the day arises where
     * such is requested, the workflow is here.
     * 
     * @param Request $request
     */
    public function params(Request $request) {
        $report = $request->input('reports');
        $class = 'App\\Http\\Controllers\\Utilities\\Reports\\' . ucFirst($report) . 'Controller';
        if (!class_exists($class) || empty(class_implements($class, 'ReportInterface'))) {
            return response('Report class ' . $request->input('reports') . ' could not be found', 404);
        }
        return $class::params($request);
    }
    
    /**
     * @function report
     * returns back the appropriate information to render a DataTable.
     * 
     * @param Request $request
     */
    public function report(Request $request) {
        $report = $request->input('reports');
        $class = 'App\\Http\\Controllers\\Utilities\\Reports\\' . ucFirst($report) . 'Controller';
        if (!class_exists($class) || empty(class_implements($class, 'ReportInterface'))) {
            return response('Report class ' . $request->input('reports') . ' could not be found', 404);
        }
        return $class::render($request);
    }
}