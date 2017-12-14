<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class SiteController extends Controller
{
    public function index()
    {
        //
        return view('site.index');
    }

    public function index_data(Request $request){
        $datas = DB::table('project_site')
            ->leftJoin('project_area', 'project_site.area_id' , '=', 'project_area.id')
            ->select(
                'project_site.id', 'project_site.name',
                'site_id_customer', 'site_alias1',
                'site_alias2', 'project_area.name as area_name')
            ->where('project_site.name', 'ilike', "%{$request->get('name')}%")
            ->orWhere('site_id_customer', 'ilike', "%{$request->get('name')}%");

        return DataTables::of($datas)
            ->addColumn('action', '<a href="\site\detail\{{$id}}" class="btn btn-primary trigger">Detail</a>')
            ->make(true);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        if(!$id){
            abort(404);
        }
        $data = DB::table('project_site')
            ->leftJoin('project_area', 'project_site.area_id' , '=', 'project_area.id')
            ->select(
                'project_site.id', 'project_site.name',
                'site_id_customer', 'site_alias1',
                'site_alias2', 'project_area.name as area_name')
            ->where('project_site.id', '=', "{$id}")->first();

        $data_po_header = DB::table('sale_order_line')
            ->leftJoin('sale_order', 'sale_order_line.order_id', '=', 'sale_order.id')
            ->leftJoin('project_project', 'sale_order_line.project_id', '=', 'project_project.id')
            ->leftJoin('project_site_type', 'project_project.site_type_id', '=', 'project_site_type.id')
            ->leftJoin('project_misc_category', 'project_project.misc_category_id', '=', 'project_misc_category.id')
            ->leftJoin('project_misc_work', 'project_project.misc_work_id', '=', 'project_misc_work.id')
            ->leftJoin('account_invoice_line', 'account_invoice_line.project_id', '=', 'project_project.id')
            ->leftJoin('account_invoice', 'account_invoice_line.invoice_id', '=', 'account_invoice.id')
            ->where('project_project.site_id', '=', "{$id}")
            ->where('sale_order.state', '=', DB::raw("'progress'"))
//            ->where('account_invoice.state', '=', DB::raw("'paid'"))
//            ->where('account_invoice.type', '=', DB::raw("'out_invoice'"))
            ->select(
                'sale_order.state',
                'sale_order_line.id',
                'sale_order_line.price_unit',
                'sale_order_line.product_uom_qty',
                'sale_order.client_order_ref',
                'project_site_type.name as site_type',
                'project_misc_category.name as work_category',
                'project_misc_work.name as work_description',
                DB::raw('sum(account_invoice.amount_total) as subtotal'))
            ->groupBy('sale_order.state',
                'sale_order_line.id',
                'sale_order_line.price_unit',
                'sale_order_line.product_uom_qty',
                'sale_order.client_order_ref',
                'project_site_type.name',
                'project_misc_category.name',
                'project_misc_work.name')->get();

        $data_po = DB::table('sale_order_line')
            ->leftJoin('sale_order', 'sale_order_line.order_id', '=', 'sale_order.id')
            ->leftJoin('project_project', 'sale_order_line.project_id', '=', 'project_project.id')
            ->leftJoin('account_invoice_line', 'account_invoice_line.project_id', '=', 'project_project.id')
            ->leftJoin('account_invoice', 'account_invoice_line.invoice_id', '=', 'account_invoice.id')
            ->leftJoin('budget_plan', 'budget_plan.project_id', '=', 'project_project.id')
            ->leftJoin('budget_plan_line', 'budget_plan_line.budget_id', '=', 'budget_plan.id')
            ->leftJoin('budget_used', 'budget_used.budget_item_id', '=', 'budget_plan_line.id')
            ->where('project_project.site_id', '=', "{$id}")
            ->where('sale_order.state', '=', DB::raw("'progress'"))
            ->wherein('account_invoice.state', ['open', 'paid'])
            ->where('account_invoice.type', '=', DB::raw("'out_invoice'"))
            ->select(
                'sale_order_line.id',
                'sale_order_line.price_unit',
                'sale_order_line.product_uom_qty',
                'account_invoice.name_sequence as no_invoice',
                'account_invoice.amount_total',
                'account_invoice.state',
                'budget_plan.state as budget_state',
                DB::raw('sum(budget_plan_line.amount) as budget_total'),
                DB::raw('sum(budget_used.amount) as realisasi'))
            ->groupBy('sale_order_line.id',
                'sale_order_line.price_unit',
                'sale_order_line.product_uom_qty',
                'account_invoice.name_sequence',
                'account_invoice.state',
                'budget_plan.state',
                'account_invoice.amount_total')
//            ->toSql();
            ->get();
//        $data_po = DB::table('budget_plan')->select('id')->get();
//        return $data_po;

        return view('site.show', compact('data','data_po_header', 'data_po'));
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}