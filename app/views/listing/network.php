<?php $this->view('partials/head'); ?>

<?php 
//Initialize models needed for the table
new Machine_model;
new Reportdata_model;
new Network_model;
?>

<div class="container">

  <div class="row">

  	<div class="col-lg-12">

		  <h3>Network report <span id="total-count" class='label label-primary'>…</span></h3>

		  <table class="table table-striped table-condensed table-bordered">
		    <thead>
		      <tr>
		      	<th data-i18n="listing.computername" data-colname='machine.computer_name'>Name</th>
		        <th data-i18n="serial" data-colname='reportdata.serial_number'>Serial</th>
		        <th data-i18n="listing.username" data-colname='reportdata.long_username'>Username</th>
		        <th data-colname='network.service'>Service</th>
		        <th data-colname='network.status'>Status</th>
		        <th data-colname='network.ethernet'>Ethernet</th>
		        <th data-colname='network.ipv4ip'>IP Address</th>
		        <th data-colname='network.ipv4router'>Router</th>
		        <th data-colname='network.ipv4mask'>Mask</th>
		      </tr>
		    </thead>
		    <tbody>
		    	<tr>
					<td data-i18n="listing.loading" colspan="9" class="dataTables_empty"></td>
				</tr>
		    </tbody>
		  </table>
    </div> <!-- /span 12 -->
  </div> <!-- /row -->
</div>  <!-- /container -->

<script type="text/javascript">

	$(document).on('appUpdate', function(e){

		var oTable = $('.table').DataTable();
		oTable.ajax.reload();
		return;

	});
	
	$(document).on('appReady', function(e, lang) {

        // Get modifiers from data attribute
        var mySort = [], // Initial sort
            hideThese = [], // Hidden columns
            col = 0, // Column counter
            runtypes = [], // Array for runtype column 
            columnDefs = [{ visible: false, targets: hideThese }]; //Column Definitions

        $('.table th').map(function(){

            columnDefs.push({name: $(this).data('colname'), targets: col});

            if($(this).data('sort')){
              mySort.push([col, $(this).data('sort')])
            }

            if($(this).data('hide')){
              hideThese.push(col);
            }

            col++
        });

	    oTable = $('.table').dataTable( {
            ajax: {
                url: "<?=url('datatables/data')?>",
                type: "POST"
            },
            dom: mr.dt.buttonDom,
            buttons: mr.dt.buttons,
            order: mySort,
            columnDefs: columnDefs,
		    createdRow: function( nRow, aData, iDataIndex ) {
	        	// Update name in first column to link
	        	var name=$('td:eq(0)', nRow).html();
	        	if(name == ''){name = "No Name"};
	        	var sn=$('td:eq(1)', nRow).html();
	        	var link = get_client_detail_link(name, sn, '<?php echo url(); ?>/', '#tab_network-tab');
	        	$('td:eq(0)', nRow).html(link);

	        	// Status
	        	var status=$('td:eq(4)', nRow).html();
	        	status = status == 1 ? '<span class="label label-success">Enabled</span>' : 
	        		(status === '0' ? '<span class="label label-danger">Disabled</span>' : '')
	        	$('td:eq(4)', nRow).html(status)

		    }
	    } );
	} );
</script>

<?php $this->view('partials/foot'); ?>
