@extends('template')

@section('main')
<div class="content-header" style="border-bottom: 1px solid rgba(1,1,1,0.1); padding-bottom: 5px !important; margin-bottom: 15px;">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h5 class="m-0 text-dark">{{ $lang('Stock Report') }}</h5>
			</div><!-- /.col -->
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="#">{{ $lang('Home') }}</a></li>
					<li class="breadcrumb-item active">{{ $lang('Stock Report') }}</li>
				</ol>
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</div>

<!-- Main content -->
<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<div class="row">
							<div class="col-md-10">
								<form action="">
									<div class="row">
										<div class="col-md-3">
											<div class="form-group m-0">
												<label>Item</label>
												<select name="item_name" class="form-control">
													<option value="">- All -</option>
													@foreach( $items_name as $item )
														@if( $item->item_name != "" )
															<option {{ @$item->item_name == @$_GET['item_name'] ? 'selected' : ''; }} value="{{ $item->item_name }}">{{ $item->item_name }}</option>
														@endif
													@endforeach
												</select>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group m-0">
												<label>Size</label>
												<select name="item_size" class="form-control">
													<option value="">- All -</option>
													@foreach( $items_size as $item )
													@if( $item->item_size != "" )
														<option {{ @$item->item_size == @$_GET['item_size'] ? 'selected' : ''; }} value="{{ $item->item_size }}">{{ $item->item_size }}</option>
													@endif
													@endforeach
												</select>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group m-0">
												<label>Color</label>
												<select name="id_color" class="form-control">
													<option value="">- All -</option>
													@foreach( $items_color as $item )
														@if( $item->id_color != "" )
															<option {{ @$item->id_color == @$_GET['id_color'] ? 'selected' : ''; }} value="{{ $item->id_color }}">{{ $item->color_name }}</option>
														@endif
													@endforeach
												</select>
											</div>
										</div>
										<div class="col-md-2 align-self-end">
											<button class="btn btn-primary">
												<i class="fa fa-search"></i>
											</button>
										</div>
									</div>
								</form>
							</div>
							<div class="col-md-2 text-right align-self-end">
								<a href="{{route('stock_report.export')}}?export=true&item_name={{@$_GET['item_name']}}&id_color={{@$_GET['id_color']}}&item_size={{@$_GET['item_size']}}" target="_blank" class="btn btn-success">
									<i class="fa fa-file-excel mr-2"></i> Export to Excel
								</a>
							</div>
						</div>
					</div>
					<div class="card-body">
						<table class="table table-striped table-hover table-bordered display nowrap" width="100%">
							<thead>
								<tr>
									<th width="30">{{$lang('No')}}</th>
									<th>{{$lang('Item Name')}}</th>
									<th>{{$lang('Description')}}</th>
									<th>{{$lang('Color')}}</th>
									<th>{{$lang('Size')}}</th>
									<th class="text-center">{{$lang('Stock')}}</th>
									<th class="text-center">{{$lang('Min Stock')}}</th>
									<th>{{$lang('Status')}}</th>
								</tr>
							</thead>
							<tbody>
								@php
									$no = 1;
								@endphp
								@foreach ($items as $item)
									<tr>
										<td>{{ $no++ }}</td>
										<td>{{ $item['item_name'] }}</td>
										<td>{{ $item['item_desc'] }}</td>
										<td>{{ $item['color_name'] }}</td>
										<td>{{ $item['item_size'] }}</td>
										<td>{{ $item['stock'] }}</td>
										<td>{{ $item['min_stock'] }}</td>
										<td>
											@if ($item['stock'] >= $item['min_stock'])
												<span class="badge badge-success">
													<i class="fa fa-check"></i>
												</span>
											@else
												<span class="badge badge-danger">
													<i class="fa fa-times"></i>
												</span>
											@endif
										</td>
									</tr>
								@endforeach
							</tbody>
							<tfoot>
								<tr>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</section>
@stop
@section("script")
<script>
	$(document).ready(function(){
		$(".table").dataTable({
			pageLength : 50,
		});
		// $(".table").dataTable({
		// 	processing: true,
		// 	serverSide: true,
		// 	ajax: {
		// 		url : "{{ route('stock_report.datatables') }}",
		// 		type : "POST",
		// 		data : {
		// 			item_name : "{{ @$_GET['item_name'] }}",
		// 			item_size : "{{ @$_GET['item_size'] }}",
		// 			id_color : "{{ @$_GET['id_color'] }}",
		// 		},
		// 		headers : {
		// 			"X-CSRF-TOKEN" : $("meta[name='csrf-token']").attr("content"),
		// 		},
		// 	},
		// 	columns: [
		// 		{'data': 'no'},
		// 		{'data': 'item_name'},
		// 		{'data': 'item_desc'},
		// 		{'data': 'item_color'},
		// 		{'data': 'item_size'},
		// 		{'className' : 'text-right', 'data': 'qty'},
		// 		{'className' : 'text-right', 'data': 'min_stock'},
		// 		{'className' : 'text-center', 'data' : 'status'},
		// 	],
		// 	pageLength: 50,
		// 	footerCallback: function (row, data, start, end, display) {
		// 			let api = this.api();
	
		// 			// Remove the formatting to get integer data for summation
		// 			let intVal = function (i) {
		// 					return typeof i === 'string'
		// 							? i.replace(/[\$,]/g, '') * 1
		// 							: typeof i === 'number'
		// 							? i
		// 							: 0;
		// 			};
	
		// 			// Total over all pages
		// 			total = api
		// 					.column(5)
		// 					.data()
		// 					.reduce((a, b) => intVal(a) + intVal(b), 0);
	
		// 			// Total over this page
		// 			pageTotal = api
		// 					.column(5, { page: 'current' })
		// 					.data()
		// 					.reduce((a, b) => intVal(a) + intVal(b), 0);
	
		// 			// Update footer
		// 			api.column(5).footer().innerHTML =
		// 					pageTotal;
		// 	},
		// });
	});
</script>
@stop