<style>
    table tbody tr.group td {
        font-weight: bold;
    }
</style>
<div style="padding: 10px;">
    <table name="data-display"  class="table-condensed table-bordered">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Price</th>
                <th>Needed</th>
                <th>Order #</th>
                <th>Web Order #</th>
                <th>Order Date</th>
                <th>Expected Date</th>
                <th>Ships In?</th>
                <th>State</th>
                <th>ItemName</th>
                <th>QuantityNeeded</th>
                <th>QOH</th>
                <th>Expected</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{$item->SKU}}</td>
                <td>{{$item->PricePerUnit}}</td>
                <td>{{$item->QuantityNeeded}}</td>
                <td>{{$item->OrderNumber}}</td>
                <td><?= empty($item->SourceOrderNumber) ? $item->SourceItemID : $item->SourceOrderNumber ?></td>
                <td><?= date('m/d/Y', strtotime($item->OrderDate)) ?></td>
                <td><?= date('m/d/Y', strtotime($item->ExpectedShipDate)) ?></td>
                <td>{{$item->Text1}}</td>
                <td>{{$item->ShipState}}</td>
                <td>{{$item->ItemName}}</td>
                <td>{{$item->QuantityNeeded}}</td>
                <td>{{$item->QOH}}</td>
                <td>{{$item->Expected}}</td>
                <td>{{$item->Message}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script language="javascript">
$(document).ready(function() {
    $('table[name=data-display]').DataTable({
        dom: '<"top"<"col-md-6"B><"col-md-6"f>>rt<"bottom" ip>',
        "drawCallback": function ( settings ) {
            var api = this.api();
            var rows = api.rows( {page:'current'} ).nodes();
            var last=null;
            var qtyNeeded = 0;
            var rowCount = api.rows().count();

            api.column(0).data().each( function ( group, i ) {
                var row = api.rows(i);
                var d = row.data();
                if ( last !== null && last !== group ) { //Prepend a row
                    $(rows).eq(i).before(
                            '<tr class="group"><td>'+d[0][0]+'</td>'+
                            '<td colspan="5">'+d[0][9]+'</td>'+
                            '<td>Need: '+qtyNeeded+'</td>'+
                            '<td>QOH: '+d[0][10]+'</td>'+
                            '<td>Expected: '+d[0][11]+'</td>'+
                        '</tr>' + 
                        '<tr class="group">' +
                            '<td colspan="8">Notes: '+d[0][12]+'</td>' + 
                        '</tr>'
                            );
                    qtyNeeded = 0; //reset.
                } else if (i+1 == rowCount) {
                    qtyNeeded = qtyNeeded + parseInt(d[0][10]);
                    $(rows).eq(i).after(
                            '<tr class="group"><td>'+d[0][0]+'</td>'+
                            '<td colspan="5">'+d[0][9]+'</td>'+
                            '<td>Need: '+qtyNeeded+'</td>'+
                            '<td>QOH: '+d[0][10]+'</td>'+
                            '<td>Expected: '+d[0][11]+'</td>'+
                        '</tr>' + 
                        '<tr class="group">' +
                            '<td colspan="8">Notes: '+d[0][12]+'</td>' + 
                        '</tr>'
                            );
                }
                qtyNeeded = qtyNeeded + parseInt(d[0][10]);
                last = group;
            });
        },
        paging: false,
        buttons: [
            {
                extend: 'csv',
                text: 'Download CSV'
            }
        ],
        "scrollY": "500px",
        "scrollX": true,
        "scrollCollapse": true,
        columnDefs: [
            { targets: [0,1,2,3,4,5,6,7,8], width: '100px'},
            { targets: [9,10,11,12,13], visible: false}
        ],
        "order": [[ 0, 'asc' ]],
    });
});
</script>