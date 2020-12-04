<?php
namespace App\Http\Controllers\Utilities\Reports;

use Illuminate\Http\Request;

interface ReportInterface {
    //returns back any rendering for parameters required to complete this report.
    public static function params(Request $request);
    //Returns the data from the report.
    public static function render(Request $request);
}