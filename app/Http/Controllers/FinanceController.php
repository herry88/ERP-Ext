<?php

namespace App\Http\Controllers;

use App\Island;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class FinanceController extends Controller
{
    public function SampleTest(){
        return Island::with('provinces.cities')->get();
    }

    public function reportprojectdetail($customer_id, $year, $site_type_id, $date_filter, $check_ignore_filter){
        $val = new stdClass();
        $val->customer_id = $customer_id;
        $val->year = $year;
        $val->site_type_id = $site_type_id;

        $resume_project = $this->_report_project_detail_data($customer_id, $year, $site_type_id, $date_filter, $check_ignore_filter);

        return view('finance.report_project_detail', compact('resume_project','val', 'date_filter', 'check_ignore_filter'));
    }

    public function reportprojectdetailexport($customer_id, $year, $site_type_id, $date_filter, $check_ignore_filter){
        if(!$customer_id || !$year || !$site_type_id){
            abort(404);
        }
        return Excel::create('reportprojectdetailexport',function($excel) use ($customer_id, $year, $site_type_id, $date_filter, $check_ignore_filter) {
            $excel->sheet('Resume Monitoring Project', function($sheet) use ($customer_id, $year, $site_type_id, $date_filter, $check_ignore_filter) {
                $sheet->setColumnFormat(array(
                    'G' => '#,##0.00',
                    'H' => '#,##0.00',
                    'I' => '#,##0.00',
                    'K' => '#,##0.00',
                    'M' => '#,##0.00',
                    'O' => '#,##0.00',
                    'P' => '#,##0.00'
                ));

                $sheet->setWidth(array(
                    'A' => 5,
                    'B' => 75,
                    'C' => 15,
                    'D' => 15,
                    'E' => 35,
                    'F' => 15,
                    'G' => 15,
                    'H' => 15,
                    'I' => 15,
                    'J' => 5,
                    'K' => 15,
                    'L' => 35,
                    'M' => 15,
                    'N' => 35,
                    'O' => 15,
                    'P' => 15,
                    'Q' => 5,
                    'R' => 15
                ));

                $row = 1;
                $sheet->Row($row++, array('Resume Monitoring Project'));
                $sheet->Row($row++, array('Customer : ' . $customer_id));
                $sheet->Row($row++, array('Tahun : ' . $year));
                $sheet->Row($row++, array('Site Type : ' . $site_type_id));

                $report_data = $this->_report_project_detail_data($customer_id, $year, $site_type_id, $date_filter, $check_ignore_filter);

                $row++;
                $sheet->Row($row++, array(
                    'No', 'Nama Site', 'Start Project',
                    'Site ID', 'Customer', 'Type Project',
                    'Estimasi Nilai PO', 'Estimasi Budget', 'Gross Margin',
                    '%', 'Realisasi Budget', 'No PO', 'Nilai PO',
                    'No Inv', 'Nilai Inv', 'Laba/Rugi', '%', 'Status Inv'
                ));
                $row_no = 1;
                $start_row = 0;
                $end_row = 7;
                $sheet->cells('A6:R6', function($cells){
                    $cells->setBorder('thin', 'thin', 'thin', 'thin');
                });
                foreach ($report_data as $data){
                    $start_row = $end_row;
                    $row_counter = 0;
                    foreach ($data->invoice_projects as $data_invoice){
                        $estimate_nilai_po = 0;
                        $amount_total = 0;
                        $sum_nilai_invoice = 0;
                        foreach ($data->budget_plans as $value){
                            $estimate_nilai_po += $value->estimate_po;
                            $amount_total += $value->amount_total;
                        }
                        foreach ($data->invoice_projects as $check_data){
                            $sum_nilai_invoice += $check_data->nilai_invoice;
                        }
                        $sheet->Row($row++, array(
                            ($row_no != $row_counter) ? $row_no : '',
                            ($row_no != $row_counter) ? $data->site_name : '',
                            ($row_no != $row_counter) ? 'Start Project' : '',
                            ($row_no != $row_counter) ?$data->project_id : '',
                            ($row_no != $row_counter) ?$data->customer_name : '',
                            ($row_no != $row_counter) ?$data->project_type : '',
                            ($row_no != $row_counter) ?$estimate_nilai_po : '',
                            ($row_no != $row_counter) ?$amount_total : '',
                            ($row_no != $row_counter && $estimate_nilai_po !=0) ?(float)($estimate_nilai_po-$amount_total) / (float)$estimate_nilai_po : 0,
                            ($row_no != $row_counter) ?'%': '',
                            ($row_no != $row_counter) ?$data->realisasi_budget : '',
                            ($row_no != $row_counter) ?$data->client_order_ref : '',
                            ($row_no != $row_counter) ?$data->nilai_po : '',
                            $data_invoice->no_invoice,
                            $data_invoice->nilai_invoice,
                            ($row_no != $row_counter) ?$sum_nilai_invoice-$data->realisasi_budget-$data->realisasi_budget : '',
                            ($row_no != $row_counter) ?($data->realisasi_budget > 0)? number_format((float)($sum_nilai_invoice-$data->realisasi_budget-$data->realisasi_budget)/(float)($data->realisasi_budget),2):0 .'%': '',
                            $data_invoice->invoice_state
                        ));
                        $row_counter = $row_no;
                        $end_row++;
                    }
                    $row_no++;
                    for($i=$start_row; $i<=$end_row; $i++){
                        if($i == $start_row){
                            $sheet->cells('A'. $i .':R'. $i, function($cells){
                                $cells->setBorder('thin', 'thin', 'none', 'thin');
                            });
                        }elseif($i == $end_row){
                            $sheet->cells('A'. $i .':R'. $i, function($cells){
                                $cells->setBorder('none', 'thin', 'none', 'thin');
                            });
                        }else{
                            $sheet->cells('A'. $i .':R'. $i, function($cells){
                                $cells->setBorder('none', 'thin', 'none', 'thin');
                            });
                        }
                    }
                }
                $sheet->cells('A'.$end_row.':R'.$end_row, function($cells){
                    $cells->setBorder('none', 'thin', 'thin', 'thin');
                });
            });
        })->download('xls');
    }

    //finance monitoring preventive
    public function monitoring_preventive(Request $request){

        $years      = $this->_get_ten_years();
        $date_filter = '01/01/2018';


        $customer_lists = DB::table('res_partner')
        ->select(
            'res_partner.id',
            'res_partner.name'
            )
            ->pluck('name', 'id');
		// return $customer_lists;

		$project_area = DB::table('project_area')
		->select(
				'project_area.id',
				'project_area.name'
				)
				->pluck('name', 'id');


        return view('finance.monitoring_preventive',compact('years', 'project_data','project_area','customer_lists'));
	}

	//monitoring preventive detail
	public function monitoring_preventive_detail(Request $request){


		return view('finance.monitoring_preventive_detail');
	}


    public function reportproject(Request $request){
        $years = $this->_get_ten_years();
        $site_types = $this->_get_site_types();
        $check_ignore_filter = 0;
        $date_filter = '01/01/2018';

        if($request->has('date_filter')){
            $date_filter = $request->input('date_filter');
        }

        if($request->has('check_ignore_filter')){
            $check_ignore_filter = ($request->input('check_ignore_filter') == 'on')?1:0;
        }

        $project_data = null;
        if($request->has('year_filter')){

            if($check_ignore_filter == 0){
                $project_on_budget = DB::table('budget_plan')
                    ->select(
                        'budget_plan.project_id'
                    )
                    ->where('budget_plan.date', '>=' , $this->_convert_date($date_filter))
                    ->distinct()
                    ->get();
//                return $project_on_budget;

//                if(!empty($project_on_budget)):
                $project_on_budget_ids = null;
                foreach ($project_on_budget as $data){
                    $project_on_budget_ids[] = $data->project_id;
                }

                $resume_project = DB::table('sale_order_line')
                    ->select(
                        'res_partner.name as customer_name',
                        'sale_order.partner_id as customer_id',
                        'project_project.site_type_id',
                        DB::raw('EXTRACT(YEAR from sale_order.date_order) as year'),
                        DB::raw('count(project_project.id) as total_project'),
                        DB::raw('sum(sale_order_line.price_unit * sale_order_line.product_uom_qty) as nilai_po')
                    )
                    ->leftJoin('sale_order', 'sale_order_line.order_id', '=', 'sale_order.id')
                    ->leftJoin('res_partner', 'sale_order.partner_id', '=', 'res_partner.id')
                    ->leftJoin('project_project', 'sale_order_line.project_id', '=', 'project_project.id')
                    ->whereRaw(DB::raw('EXTRACT(YEAR from sale_order.date_order) = ' . $request->input('year_filter')))
                    ->where('project_project.site_type_id', '=',$request->input('site_type_filter'))
                    ->whereIn('sale_order_line.project_id', $project_on_budget_ids)
                    ->groupBy(
                        'res_partner.name',
                        DB::raw('EXTRACT(YEAR from sale_order.date_order)'),
                        'sale_order.partner_id',
                        'project_project.site_type_id'
                    )
                    ->get();
//                else:
//                    $resume_project = null;
//                endif;
                //test
            }else{
                $resume_project = DB::table('sale_order_line')
                    ->select(
                        'res_partner.name as customer_name',
                        'sale_order.partner_id as customer_id',
                        'project_project.site_type_id',
                        DB::raw('EXTRACT(YEAR from sale_order.date_order) as year'),
                        DB::raw('count(project_project.id) as total_project'),
                        DB::raw('sum(sale_order_line.price_unit * sale_order_line.product_uom_qty) as nilai_po')
                    )
                    ->leftJoin('sale_order', 'sale_order_line.order_id', '=', 'sale_order.id')
                    ->leftJoin('res_partner', 'sale_order.partner_id', '=', 'res_partner.id')
                    ->leftJoin('project_project', 'sale_order_line.project_id', '=', 'project_project.id')
                    ->whereRaw(DB::raw('EXTRACT(YEAR from sale_order.date_order) = ' . $request->input('year_filter')))
                    ->where('project_project.site_type_id', '=',$request->input('site_type_filter'))
                    ->groupBy(
                        'res_partner.name',
                        DB::raw('EXTRACT(YEAR from sale_order.date_order)'),
                        'sale_order.partner_id',
                        'project_project.site_type_id'
                    )
                    ->get();
            }



            $total_penagihan = DB::table('sale_order_line')
                ->select(
                    'res_partner.name as customer_name',
                    'sale_order.partner_id as customer_id',
                    'project_project.site_type_id',
                    DB::raw('EXTRACT(YEAR from sale_order.date_order) as year'),
                    DB::raw('sum(account_invoice_line.price_subtotal) as nilai_penagihan')
                )
                ->leftJoin('sale_order', 'sale_order_line.order_id', '=', 'sale_order.id')
                ->leftJoin('res_partner', 'sale_order.partner_id', '=', 'res_partner.id')
                ->leftJoin('project_project', 'sale_order_line.project_id', '=', 'project_project.id')
                ->leftJoin('account_invoice_line', 'sale_order_line.project_id', '=', 'account_invoice_line.project_id')
                ->leftJoin('account_invoice', 'account_invoice.id', '=', 'account_invoice_line.invoice_id')
                ->whereRaw(DB::raw('EXTRACT(YEAR from sale_order.date_order) = ' . $request->input('year_filter')))
                ->where('project_project.site_type_id', '=',$request->input('site_type_filter'))
                ->where('account_invoice.type', '=', 'out_invoice')
                ->whereIn('account_invoice.state', ['open', 'received', 'paid', 'confirmed'])
                ->groupBy(
                    'res_partner.name',
                    DB::raw('EXTRACT(YEAR from sale_order.date_order)'),
                    'sale_order.partner_id',
                    'project_project.site_type_id'
                )
                ->get();

            $total_budget = DB::table('sale_order_line')
                ->select(
                    'res_partner.name as customer_name',
                    'sale_order.partner_id as customer_id',
                    'project_project.site_type_id',
                    DB::raw('EXTRACT(YEAR from sale_order.date_order) as year'),
                    DB::raw('sum(budget_plan_line.amount) as nilai_budget')
                )
                ->leftJoin('sale_order', 'sale_order_line.order_id', '=', 'sale_order.id')
                ->leftJoin('res_partner', 'sale_order.partner_id', '=', 'res_partner.id')
                ->leftJoin('project_project', 'sale_order_line.project_id', '=', 'project_project.id')
                ->leftJoin('budget_plan', 'budget_plan.project_id', 'sale_order_line.project_id')
                ->leftJoin('budget_plan_line', 'budget_plan_line.budget_id', 'budget_plan.id')
                ->whereRaw(DB::raw('EXTRACT(YEAR from sale_order.date_order) = ' . $request->input('year_filter')))
                ->where('project_project.site_type_id', '=',$request->input('site_type_filter'))
                ->groupBy(
                    'res_partner.name',
                    DB::raw('EXTRACT(YEAR from sale_order.date_order)'),
                    'sale_order.partner_id',
                    'project_project.site_type_id'
                )
                ->get();

            $total_budget_request = DB::table('sale_order_line')
                ->select(
                    'res_partner.name as customer_name',
                    'sale_order.partner_id as customer_id',
                    'project_project.site_type_id',
                    DB::raw('EXTRACT(YEAR from sale_order.date_order) as year'),
                    DB::raw('sum(budget_used_request.request) as nilai_budget_request')
                )
                ->leftJoin('sale_order', 'sale_order_line.order_id', '=', 'sale_order.id')
                ->leftJoin('res_partner', 'sale_order.partner_id', '=', 'res_partner.id')
                ->leftJoin('project_project', 'sale_order_line.project_id', '=', 'project_project.id')
                ->leftJoin('budget_plan', 'budget_plan.project_id', '=', 'sale_order_line.project_id')
                ->leftJoin('budget_plan_line', 'budget_plan_line.budget_id', '=', 'budget_plan.id')
                ->leftJoin('budget_used_request', 'budget_plan_line.id', '=', 'budget_used_request.budget_item_id')
                ->whereRaw(DB::raw('EXTRACT(YEAR from sale_order.date_order) = ' . $request->input('year_filter')))
                ->where('project_project.site_type_id', '=',$request->input('site_type_filter'))
                ->groupBy(
                    'res_partner.name',
                    DB::raw('EXTRACT(YEAR from sale_order.date_order)'),
                    'sale_order.partner_id',
                    'project_project.site_type_id'
                )
                ->get();

            foreach ($resume_project as $data){
                foreach ($total_penagihan as $check_data){
                    if($check_data->customer_id == $data->customer_id && $check_data->site_type_id == $data->site_type_id && $check_data->year == $data->year){
                        $data->nilai_penagihan = $check_data->nilai_penagihan;
                        $data->persen_nilai_penagihan = ($data->nilai_po > 0)? ((float) $check_data->nilai_penagihan / (float)$data->nilai_po) * 100: 0;
                        break;
                    }
                }

                foreach ($total_budget as $check_data){
                    if($check_data->customer_id == $data->customer_id && $check_data->site_type_id == $data->site_type_id && $check_data->year == $data->year){
                        $data->nilai_budget = $check_data->nilai_budget;
                        break;
                    }
                }

                foreach ($total_budget_request as $check_data){
                    if($check_data->customer_id == $data->customer_id && $check_data->site_type_id == $data->site_type_id && $check_data->year == $data->year){
                        $data->nilai_budget_request = 0-$check_data->nilai_budget_request;
                        $data->persen_nilai_budget_request = ($data->nilai_budget > 0) ? ((float) 0-$check_data->nilai_budget_request / (float) $data->nilai_budget) * 100: 0;
                        break;
                    }
                }
            }

//            return $total_penagihan;

            $project_data = $resume_project;
        }

        $date_filter_decode = $this->_convert_date($date_filter);
        return view('finance.report_project', compact('years', 'site_types', 'project_data',
            'date_filter_decode', 'date_filter', 'check_ignore_filter'));
    }

    public function reportBudgetDept(){
        $budget_by_years = DB::table('budget_plan')
            ->select(
                DB::raw('EXTRACT(YEAR from periode_start) as tahun'),
                DB::raw('sum(budget_plan_line.amount) as total')
            )
            ->leftJoin('hr_department', 'budget_plan.department_id', '=', 'hr_department.id')
            ->leftJoin('budget_plan_line', 'budget_plan_line.budget_id', '=', 'budget_plan.id')
            ->groupBy(
                DB::raw('EXTRACT(YEAR from periode_start)')
            )
            ->orderBy(
                DB::raw('EXTRACT(YEAR from periode_start)')
            )
            ->where('budget_plan.type', '=', 'department')
            ->whereNotNull(DB::raw('EXTRACT(YEAR from periode_start)'))
            ->get();

        $budget_years = null;
        foreach ($budget_by_years as $data){
            $budget_years[] = $data->tahun;
        }

        $budget_realizations = DB::table('budget_used_request')
            ->select(
                DB::raw('EXTRACT(YEAR from periode_start) as tahun'),
                DB::raw('sum(budget_used_request.request) as total')
            )
            ->leftJoin('budget_plan_line', 'budget_used_request.budget_item_id', '=', 'budget_plan_line.id')
            ->leftJoin('budget_plan', 'budget_plan_line.budget_id', 'budget_plan.id')
            ->groupBy(
                DB::raw('EXTRACT(YEAR from periode_start)')
            )
            ->whereIn(DB::raw('EXTRACT(YEAR from periode_start)'), $budget_years)
            ->where('budget_plan.type', '=', 'department')
            ->get();

        foreach ($budget_by_years as $data){
            $request = null;
            foreach ($budget_realizations as $check){
                if($check->tahun == $data->tahun){
                    $request = $check->total;
                    break;
                }
            }
            $data->realization = 0-$request;
        }

//        return $budget_by_years;

        return view('finance.report_budget_dept', compact('budget_by_years'));
    }

    public function reportBudgetDeptDetail($tahun){
        $budget_plan_line_datas = DB::table('budget_plan')
            ->select(
                'budget_plan_line.id',
                'hr_department.name as department_name',
                'budget_plan.id as budget_plan_id',
                'budget_plan.date',
                'budget_plan.periode_start',
                'budget_plan.periode_end',
                'budget_plan_line_view.name as budget_view_name',
                'budget_plan_line.name',
                'budget_plan_line.amount'
            )
            ->leftJoin('budget_plan_line', 'budget_plan_line.budget_id', '=', 'budget_plan.id')
            ->leftJoin('hr_department', 'hr_department.id', '=', 'budget_plan.department_id')
            ->leftJoin('budget_plan_line as budget_plan_line_view', 'budget_plan_line.parent_id', 'budget_plan_line_view.id')
            ->where('budget_plan_line.type', '<>', 'view')
            ->where('budget_plan.type', '=', 'department')
            ->where(DB::raw('EXTRACT(YEAR from budget_plan.periode_start)'), '=', $tahun)
            ->orderBy('hr_department.name', 'asc')
            ->orderBy('budget_plan.date', 'asc')
            ->get();

        $budget_plan_ids = null;

        foreach ($budget_plan_line_datas as $data){
            $budget_plan_ids[$data->id] = $data->id;
        }

        $budget_plan_request = DB::table('budget_used_request')
            ->select(
                'budget_used_request.budget_item_id',
                DB::raw('sum(budget_used_request.request) as total')
            )
            ->groupBy(
                'budget_used_request.budget_item_id'
            )
            ->whereIn('budget_used_request.budget_item_id', $budget_plan_ids)
            ->get();

        $budget_plan_line_departments = $this->_reportBudgetDeptDetailGetDeptName($budget_plan_line_datas, $budget_plan_request);

        return view('finance.report_budget_dept_detail', compact('tahun', 'budget_plan_line_datas',
            'budget_plan_line_departments'));
    }



    private function _reportBudgetDeptDetailGetDeptName($datas, $budget_plan_request){
        $value = null;
        foreach ($datas as $data){
            $isfound = false;
            if($value){
                foreach ($value as $check){
                    if($check['department_name'] == $data->department_name){
                        $isfound = true;
                        break;
                    }
                }
            }
            if(!$isfound){
                $value_periode = $this->_getPeriodes($datas, $data, $budget_plan_request);
                $value[] = array('department_name' => $data->department_name, 'periodes' => $value_periode);
            }

        }
        return $value;
    }

    /**
     * @param $datas
     * @param $data
     * @return array|null
     */
    private function _getPeriodes($datas, $data, $budget_plan_request)
    {
        $value_periode = null;
        foreach ($datas as $data_check_periode) {
            if ($data_check_periode->department_name == $data->department_name) {
                $isfound_periode = false;
                if ($value_periode) {
                    foreach ($value_periode as $check_periode) {
                        if ($check_periode['date'] == $data_check_periode->date) {
                            $isfound_periode = true;
                            break;
                        }
                    }
                }
                if (!$isfound_periode) {
                    $value_budget_data = null;
                    foreach ($datas as $data_check_budget_data){
                        if($data_check_budget_data->department_name == $data->department_name &&
                            $data_check_budget_data->date == $data_check_periode->date){

                            $nilai_pengajuan = 0;
                            foreach ($budget_plan_request as $nilai_pengajuan_data){
                                if($nilai_pengajuan_data->budget_item_id == $data_check_budget_data->budget_plan_id){
                                    $nilai_pengajuan = $nilai_pengajuan_data->total;
                                    break;
                                }
                            }

                            $value_budget_data[] = array(
                                'budget_view_name' => $data_check_budget_data->budget_view_name,
                                'name' => $data_check_budget_data->name,
                                'amount' => $data_check_budget_data->amount,
                                'nilai_pengajuan' => $nilai_pengajuan,
                                'sisa_budget' => $data_check_budget_data->amount + $nilai_pengajuan,
//                                ((float)($data_check_budget_data->amount + $nilai_pengajuan)) / ((float)$data_check_budget_data->amount) * 100
                                'persentase_budget' => 9999
                            );
                        }
                    }
                    $value_periode[] = array('date' => $data_check_periode->date,
                        'periode_start' => $data_check_periode->periode_start,
                        'periode_end' => $data_check_periode->periode_end,
                        'datas' => $value_budget_data);
                }
            }
        }
        return $value_periode;
    }

    private function _get_ten_years(){
        $currentYear = date("Y") - 5;
        $years[$currentYear] = $currentYear;
        for ($i=1; $i<9; $i++){
            $years[$currentYear+$i] = $currentYear+$i;
        }
        return $years;
    }

    private function _get_site_types(){
        return DB::table('project_site_type')
            ->whereNotIn('id', [9, 36, 37, 38, 40, 41, 42, 43, 44, 45, 46, 47, 48, 61])
            ->select('id', 'name')
            ->pluck('name', 'id');
    }

    /**
     * @param $customer_id
     * @param $year
     * @param $site_type_id
     * @return mixed
     */
    private function _report_project_detail_data($customer_id, $year, $site_type_id, $date_filter, $check_ignore_filter)
    {
        if($check_ignore_filter == 0) {
            $project_on_budget = DB::table('budget_plan')
                ->select(
                    'budget_plan.project_id'
                )
                ->where('budget_plan.date', '>=', $date_filter)
                ->distinct()
                ->get();

            $project_on_budget_ids = null;
            foreach ($project_on_budget as $data) {
                $project_on_budget_ids[] = $data->project_id;
            }

            $resume_project = DB::table('sale_order_line')
                ->select(
                    'sale_order_line.id',
                    'sale_order_line.project_id',
                    'project_project.id as site_project_id',
                    'project_site.name as site_name',
                    'account_analytic_account.name as project_id',
                    'res_partner.name as customer_name',
                    'project_site_type.name as project_type',
                    'sale_order_line.price_unit as nilai_po',
                    'sale_order.client_order_ref'
                )
                ->leftJoin('sale_order', 'sale_order_line.order_id', '=', 'sale_order.id')
                ->leftJoin('res_partner', 'sale_order.partner_id', '=', 'res_partner.id')
                ->leftJoin('project_project', 'sale_order_line.project_id', '=', 'project_project.id')
                ->leftJoin('project_site', 'project_project.site_id', '=', 'project_site.id')
                ->leftJoin('account_analytic_account', 'project_project.analytic_account_id', '=', 'account_analytic_account.id')
                ->leftJoin('project_site_type', 'project_project.site_type_id', '=', 'project_site_type.id')
                ->whereRaw(DB::raw('EXTRACT(YEAR from sale_order.date_order) = ' . $year))
                ->where('project_project.site_type_id', '=', $site_type_id)
                ->where('sale_order.partner_id', '=', $customer_id)
                ->whereIn('sale_order_line.project_id', $project_on_budget_ids)
                ->orderBy('project_site.name', 'asc')
                ->get();

        }else{
            $resume_project = DB::table('sale_order_line')
                ->select(
                    'sale_order_line.id',
                    'sale_order_line.project_id',
                    'project_project.id as site_project_id',
                    'project_site.name as site_name',
                    'account_analytic_account.name as project_id',
                    'res_partner.name as customer_name',
                    'project_site_type.name as project_type',
                    'sale_order_line.price_unit as nilai_po',
                    'sale_order.client_order_ref'
                )
                ->leftJoin('sale_order', 'sale_order_line.order_id', '=', 'sale_order.id')
                ->leftJoin('res_partner', 'sale_order.partner_id', '=', 'res_partner.id')
                ->leftJoin('project_project', 'sale_order_line.project_id', '=', 'project_project.id')
                ->leftJoin('project_site', 'project_project.site_id', '=', 'project_site.id')
                ->leftJoin('account_analytic_account', 'project_project.analytic_account_id', '=', 'account_analytic_account.id')
                ->leftJoin('project_site_type', 'project_project.site_type_id', '=', 'project_site_type.id')
                ->whereRaw(DB::raw('EXTRACT(YEAR from sale_order.date_order) = ' . $year))
                ->where('project_project.site_type_id', '=', $site_type_id)
                ->where('sale_order.partner_id', '=', $customer_id)
                ->orderBy('project_site.name', 'asc')
                ->get();
        }

        $project_ids = null;
        foreach ($resume_project as $data) {
            $isFound = False;
            if ($project_ids) {
                foreach ($project_ids as $check) {
                    if ($check == $data->project_id) {
                        $isFound = True;
                    }
                }
            }
            if ($isFound == False) {
                $project_ids[] = $data->site_project_id;
            }
        }

        $invoice_project = DB::table('sale_order_line')
            ->select(
                'sale_order_line.project_id',
                'sale_order_line.id',
                'account_invoice.name as no_invoice',
                'account_invoice.state',
                'account_invoice_line.price_subtotal as nilai_invoice'
            )
            ->leftJoin('sale_order', 'sale_order_line.order_id', '=', 'sale_order.id')
            ->leftJoin('sale_order_line_invoice_rel', 'sale_order_line_invoice_rel.order_line_id', '=', 'sale_order_line.id')
            ->leftJoin('account_invoice_line', 'sale_order_line_invoice_rel.invoice_id', '=', 'account_invoice_line.id')
            ->leftJoin('account_invoice', 'account_invoice.id', '=', 'account_invoice_line.invoice_id')
            ->whereIn('sale_order_line.project_id', $project_ids)
            ->where('account_invoice.type', '=', 'out_invoice')
            ->whereIn('account_invoice.state', ['open', 'received', 'paid', 'confirmed'])
            ->get();

//        return $invoice_project;

        $project_budget_data = DB::table('budget_plan')
            ->select(
                'budget_plan.id',
                'budget_plan.project_id',
                'budget_plan.estimate_po',
                DB::raw('sum(budget_plan_line.amount) as amount_total')
            )
            ->leftJoin('budget_plan_line', 'budget_plan.id', '=', 'budget_plan_line.budget_id')
            ->groupBy(
                'budget_plan.id',
                'budget_plan.project_id',
                'budget_plan.estimate_po'
            )
            ->whereIn('project_id', $project_ids)
            ->get();

        $project_budget_used_request_data = DB::table('budget_plan')
            ->select(
                'budget_plan.id',
                'budget_plan.project_id',
//                'budget_used_request.request'
                DB::raw('sum(budget_used_request.request) as amount_total')
            )
            ->leftJoin('budget_plan_line', 'budget_plan.id', '=', 'budget_plan_line.budget_id')
            ->leftJoin('budget_used_request', 'budget_plan_line.id', '=', 'budget_used_request.budget_item_id')
            ->groupBy(
                'budget_plan.id',
                'budget_plan.project_id'
            )
            ->whereIn('project_id', $project_ids)
            ->get();

        foreach ($resume_project as $data) {
            $budget_plan = new stdClass();
//            $budget_plan = null;
            $id = 1;
            foreach ($project_budget_data as $check) {
                if ($data->site_project_id == $check->project_id) {
                    $object = new stdClass();
                    $object->estimate_po = $check->estimate_po;
                    $object->amount_total = $check->amount_total;
                    $budget_plan->$id = $object;
                    ++$id;
                }
            }
            $check = (array)$budget_plan;
            if (empty($check)) {
                $ids = 1;
                $object = new stdClass();
                $object->estimate_po = 0;
                $object->amount_total = 0;
                $budget_plan->$ids = $object;
            }

            $data->budget_plans = $budget_plan;

            $invoice_project_list = new stdClass();
            $ids = 0;
            foreach ($invoice_project as $check) {
                if ($data->site_project_id == $check->project_id) {
                    $object = new stdClass();
                    $object->id = $check->id;
                    $object->no_invoice = $check->no_invoice;
                    $object->invoice_state = $check->state;
                    $object->nilai_invoice = $check->nilai_invoice;
                    ++$ids;
                    $invoice_project_list->$ids = $object;
                }
            }
            $check = (array)$invoice_project_list;
            if (empty($check)) {
                $ids = 1;
                $object = new stdClass();
                $object->id = null;
                $object->no_invoice = null;
                $object->invoice_state = null;
                $object->nilai_invoice = 0;
                $invoice_project_list->$ids = $object;
            }
            $data->invoice_projects = $invoice_project_list;

            $sum_realisasi_budget = 0;
            foreach ($project_budget_used_request_data as $check) {
                if ($data->site_project_id == $check->project_id) {
                    $sum_realisasi_budget += $check->amount_total;
                }
            }
            $data->realisasi_budget = $sum_realisasi_budget;
        }
        return $resume_project;
    }

    private function _convert_date($value){
        $retVal = explode("/", $value);
        return $retVal[2] . "-" . $retVal[0] . "-" . $retVal[1];
    }
}
