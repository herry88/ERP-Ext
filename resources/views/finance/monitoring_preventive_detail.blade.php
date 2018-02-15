@extends('layout')

@section('title')
    Report Project
@endsection

@push('css-head')
<style type="text/css">
        .form-label{
            margin-bottom: 0;
            font-weight: 300;
        }

        .form-control-label {
            margin-bottom: 0;
            text-align: left;
        }

        table, thead, tbody, th, td {
            border: 1px solid black !important;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-body">
				 <div class="nav-tabs-custom">
					 <ul class="nav nav-tabs">
                                <li class="active"><a href="#tab_1" data-toggle="tab">Januari - April</a></li>
                                <li><a href="#tab_2" data-toggle="tab">Mei - Agustus</a></li>
                                <li><a href="#tab_3" data-toggle="tab">September - Desember</a></li>
                            </ul>
						<div class="tab-content">
							<div class="tab-pane active" id="tab_1">
							<table class="table table-bordered ">
								<tr>
									<td rowspan="2" class="text-center" >Nomor PO </td>
									<td colspan="2">Januari</td>
									<td colspan="2">Februari</td>
									<td colspan="2">Maret</td>
									<td colspan="2">April</td>
								</tr>
								<tr>
									<td>Nomor PO</td>	
									<td>Nilai PO</td>
									<td>Nomor PO</td>	
									<td>Nilai PO</td>
									<td>Nomor PO</td>	
									<td>Nilai PO</td>
									<td>Nomor PO</td>	
									<td>Nilai PO</td>									
								</tr>
							
								<tr>
									<td class="text-center">026/OM/BBSC/I-2016/Collo-TSEL</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
								</tr>
								<tr>
								</tr>
								
							</table>
						</div>
					<!-- End  Januari - April -->
						
						<div class="tab-pane" id="tab_2">
							<table class="table table-bordered ">
								<tr>
									<td rowspan="2" class="text-center" >Nomor PO </td>
									<td colspan="2">Mei</td>
									<td colspan="2">Juni</td>
									<td colspan="2">Juli</td>
									<td colspan="2">Agustus</td>
								</tr>
								<tr>
									<td>Nomor PO</td>	
									<td>Nilai PO</td>
									<td>Nomor PO</td>	
									<td>Nilai PO</td>
									<td>Nomor PO</td>	
									<td>Nilai PO</td>
									<td>Nomor PO</td>	
									<td>Nilai PO</td>									
								</tr>
							
								<tr>
									<td class="text-center">026/OM/BBSC/I-2016/Collo-TSEL</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
								</tr>
								<tr>
								</tr>
								
							</table>
						</div>
						<!-- End Mei - Agustus -->
						
						<div class="tab-pane" id="tab_3">
							<table class="table table-bordered ">
								<tr>
									<td rowspan="2" class="text-center" >Nomor PO </td>
									<td colspan="2">September</td>
									<td colspan="2">Oktober</td>
									<td colspan="2">November</td>
									<td colspan="2">Desember</td>
								</tr>
								<tr>
									<td>Nomor PO</td>	
									<td>Nilai PO</td>
									<td>Nomor PO</td>	
									<td>Nilai PO</td>
									<td>Nomor PO</td>	
									<td>Nilai PO</td>
									<td>Nomor PO</td>	
									<td>Nilai PO</td>									
								</tr>
							
								<tr>
									<td class="text-center">026/OM/BBSC/I-2016/Collo-TSEL</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
									<td>1.200.0000</td>
								</tr>
								<tr>
								</tr>
								
							</table>
						</div>
						<!-- September - Desember -->
					
					</div>
				</div>						
                </div>
            </div>
        </div>
    </div>
@endsection
