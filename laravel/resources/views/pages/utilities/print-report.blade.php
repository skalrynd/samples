<script src="{{ asset('assets/js/utilities/printReport.js') }}?t=<?=time()?>"></script>
<style type="text/css">
    .generate-btn-container .btn {
        height: 30px;
        font-size: 14px;
    }
</style>
<form id="report">
    <div class="search-controls">   
        <div class="search-controls-bar">
            <div class="row">
                <div class="col-md-2">
                    <label>Select Report: </label>
                </div>
                <div class="col-md-6">
                    <select name="reports" class="form-control">
                        <option></option>
                        <option value="badAddresses">Bad Addresses</option>
                        <option value="duplicateOrders">Duplicate Orders</option>
                        <option value="dropShip">Drop Ship</option>
                        <option value="dropShipFlorida">Drop Ship - Florida</option>
                        <option value="dropShipFourWinds">Drop Ship - Four Winds</option>
                        <option value="fraudOrders">Find Fraud Orders</option>
                        <option value="stateRestrictions">Find State Restrictions</option>
                        <option value="noGrowingZone">No Growing Zone</option>
                        <option value="partialBOs">Partial BOs</option>
                    </select>
                </div>
                <div class="col-md-3" id="print-report-select-container">
                    <div class="generate-btn-container"></div>
                </div>
            </div>
        </div>
    </div>
    <div style="display:none;" class="params-container section section-white"></div>
</form>
<div class="data-container"></div>
<script language="javascript">
printReport.init({baseURL: '/utilities/print-reports'});
</script>