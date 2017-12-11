@extends('layout')

@section('title')
   Dashboard
@endsection

@section('content-header')

@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Sales Order</h3>
                    {{--<div class="box-tools pull-right">--}}
                        {{--<div class="has-feedback">--}}
                            {{--<input type="text" class="form-control input-sm" placeholder="Search Keyword">--}}
                            {{--<span class="glyphicon glyphicon-search form-control-feedback"></span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-5 col-md-offset-3">
                            <form method="POST" id="search-form" role="form">
                                <div class="input-group input-group-sm">
                                    <input type="text" id="name" name="name" class="form-control" placeholder="Search keyword">
                                    <span class="input-group-btn">
                                        {{--<button type="button" class="btn btn-info btn-flat">Search</button>--}}
                                        <button type="submit" class="btn btn-primary">Search</button>
                                    </span>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div>
                        <br/>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered" id="sale_orders-table">
                                <thead>
                                <tr>
                                    <th>Nomor SO</th>
                                    <th>Nomor PO</th>
                                    <th>Customer</th>
                                    <th>Nilai PO</th>
                                    <th>Tanggal PO</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

    {{--{!! $dataTable->scripts() !!}--}}
    <script>
        $(document).ready(function() {
            var oTable = $('#sale_orders-table').DataTable({
                processing  : true,
                serverSide  : true,
                responsive  : true,
                scrollX     : true,
                lengthChange: false,
                info        : true,
                autoWidth   : false,
                searching   : false,
                autoFill    : true,
                columnDefs: [
                    // { targets: [3], className: 'dt-body-right' }
                    { targets: [3], className: 'text-right' }
                ],
                ajax:$.fn.dataTable.pipeline( {
                    url: '{!! route('dashboard.data') !!}',
                    data: function(d){
                        d.name = $('input[name=name]').val();
                    },
                    pages: 5 // number of pages to cache
                }),
                {{--ajax: '{!! route('dashboard.data') !!}',--}}
                columns: [
                    { data: 'no_so', name: 'sale_order.name' },
                    { data: 'client_order_ref', name: 'client_order_ref' },
                    { data: 'name', name: 'name' },
                    { data: 'amount_total', name: 'amount_total', render: $.fn.dataTable.render.number( '.', ',', 0) },
                    { data: 'date_order', name: 'date_order' },
                    { data: 'action', name: 'action' }
                ]
            });

            $('#search-form').on('submit', function(e) {
                oTable.clearPipeline();
                oTable.draw();
                e.preventDefault();
            });
        });
    </script>
@endpush
