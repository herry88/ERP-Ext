<?php

namespace App\Http\Controllers;

use App\Island;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function SampleTest(){
        return Island::with('provinces.cities')->get();
    }

    public function reportprojectdetail($customer_id, $year, $site_type_id){
        $resume_project = DB::table('sale_order_line')
            ->select(
                'sale_order_line.id',
                'sale_order_line.project_id',
                'project_site.name as site_name',
                'account_analytic_account.name as project_id',
                'res_partner.name as customer_name',
                'project_site_type.name as project_type',
                'sale_order_line.price_unit as nilai_po'
            )
            ->leftJoin('sale_order', 'sale_order_line.order_id', '=', 'sale_order.id')
            ->leftJoin('res_partner', 'sale_order.partner_id', '=', 'res_partner.id')
            ->leftJoin('project_project', 'sale_order_line.project_id', '=', 'project_project.id')
            ->leftJoin('project_site','project_project.site_id', '=', 'project_site.id')
            ->leftJoin('account_analytic_account', 'project_project.analytic_account_id', '=', 'account_analytic_account.id')
            ->leftJoin('project_site_type', 'project_project.site_type_id', '=', 'project_site_type.id')
            ->whereRaw(DB::raw('EXTRACT(YEAR from sale_order.date_order) = ' . $year))
            ->where('project_project.site_type_id', '=',$site_type_id)
            ->where('sale_order.partner_id', '=', $customer_id)
            ->orderBy( 'project_site.name', 'asc')
            ->get();

        return view('finance.report_project_detail', compact('resume_project'));
    }

    public function reportproject(Request $request){
        $years = $this->_get_ten_years();
        $site_types = $this->_get_site_types();
        $project_data = null;
        if($request->has('year_filter')){
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

            $project_data = $resume_project;
        }
        return view('finance.report_project', compact('years', 'site_types', 'project_data'));
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
            $budget_plan_ids[] = $data->budget_plan_id;
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
}
