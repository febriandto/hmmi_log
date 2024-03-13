<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Http\Request;

use DataTables;
use Auth;

use App\Models\Stock;
use App\Models\Item;
use DB;

require "./simplexlsxgen/src/SimpleXLSXGen.php";
use Shuchkin\SimpleXLSXGen;

class StockReportController extends Controller
{


    // Deperecated
    public function datatables(Request $request)
    {

        $length = @$request->length == '' ? 25 : $request->length;
        $start  = @$request->start == '' ? 0 : $request->start;
        $data   = [];
        $no     = 1 + ($request->start);

        $query = Stock::select('id_stock','item_id','qty','input_by','input_date')->with("item.color");

        if( @$request->item_name != ""  ){

            $query = $query->whereHas('item', function($q) use ($request){
                $q->where('item_name', $request->item_name);
            });

        }

        if( @$request->item_size != ""  ){

            $query = $query->whereHas('item', function($q) use ($request){
                $q->where('item_size', $request->item_size);
            });

        }

        if( @$request->id_color != ""  ){

            $query = $query->whereHas('item', function($q) use ($request){
                $q->where('id_color', $request->id_color);
            });

        }

        foreach(  $query->get() as $key => $value ){
            $list['no'] = $no++;
            $list['item_name']  = $value->item->item_name;
            $list['item_desc']  = $value->item->item_desc;
            $list['item_color'] = @$value->item->color->color_name ?? "-";
            $list['item_size']  = $value->item->item_size;
            $list['qty']        = number_format($value->qty);
            $list['min_stock']  = number_format($value->item->min_stock);

            if( $value->qty < $value->min_stock ){
                $list['status'] = '<span class="badge badge-warning">need to order</span>';
            }else{
                $list['status'] = '<span class="badge badge-success">ok</span>';
            }

            
            $data[] = $list;

        }

        return DataTables::of($data)->escapeColumns([])->make(true);

    }

    public function index()
    {

        $stocks = [];
        $get_stocks = Stock::where("qty", ">", 0)->get();
        foreach( $get_stocks as $stock ){
            $stocks[$stock->item_id] = $stock->qty;
        }
        
        $items = [];

        $item_master = Item::where([
            "is_delete" => 'N',
        ])->with("color");
        
        // Item Name
        if(@$_GET['item_name'] != ""){
            $item_master = $item_master->where("item_name", $_GET['item_name']);
        }

        // Item Size
        if(@$_GET['item_size'] != ""){
            $item_master = $item_master->where("item_size", $_GET['item_size']);
        }

        // Color
        if(@$_GET['id_color'] != ""){
            $item_master = $item_master->where("id_color", $_GET['id_color']);
        }

        $item_master = $item_master->get();

        foreach( $item_master as $item ){

            $items[$item->item_id]['item_id'] = $item->item_id;
            $items[$item->item_id]['item_name'] = $item->item_name;
            $items[$item->item_id]['item_desc'] = $item->item_desc;
            $items[$item->item_id]['item_size'] = $item->item_size;
            $items[$item->item_id]['color_name'] = @$item->color->color_name ?? " - ";
            $items[$item->item_id]['min_stock'] = $item->min_stock ?? 0;
            $items[$item->item_id]['max_stock'] = $item->max_stock ?? 0;
            $items[$item->item_id]['stock'] = @$stocks[$item->item_id] ?? 0;
        }
        
        $items_name = DB::select(DB::raw(" SELECT DISTINCT item_name FROM m_item WHERE is_delete = 'N' "));
        $items_size = DB::select(DB::raw(" SELECT DISTINCT item_size FROM m_item WHERE is_delete = 'N' "));
        // $items_color = DB::select(DB::raw(" SELECT DISTINCT item_color FROM m_item WHERE is_delete = 'N' "));
        $items_color = Color::where("is_delete", "N")->get();

        /**
         * Export Section
         * */ 
        
        return view("pages.stock_report.index", compact("items_name", "items_size", "items_color", "items"));

    }

    public function export(Request $request)
    {
        $stocks = [];
        $get_stocks = Stock::where("qty", ">", 0)->get();
        foreach( $get_stocks as $stock ){
            $stocks[$stock->item_id] = $stock->qty;
        }
        
        $items = [];

        $item_master = Item::where([
            "is_delete" => 'N',
        ])->with("color");
        
        // Item Name
        if(@$_GET['item_name'] != ""){
            $item_master = $item_master->where("item_name", $_GET['item_name']);
        }

        // Item Size
        if(@$_GET['item_size'] != ""){
            $item_master = $item_master->where("item_size", $_GET['item_size']);
        }

        // Color
        if(@$_GET['id_color'] != ""){
            $item_master = $item_master->where("id_color", $_GET['id_color']);
        }

        $item_master = $item_master->get();

        foreach( $item_master as $item ){

            $items[$item->item_id]['item_id'] = $item->item_id;
            $items[$item->item_id]['item_name'] = $item->item_name;
            $items[$item->item_id]['item_desc'] = $item->item_desc;
            $items[$item->item_id]['item_size'] = $item->item_size;
            $items[$item->item_id]['color_name'] = @$item->color->color_name ?? " - ";
            $items[$item->item_id]['min_stock'] = $item->min_stock ?? 0;
            $items[$item->item_id]['max_stock'] = $item->max_stock ?? 0;
            $items[$item->item_id]['stock'] = @$stocks[$item->item_id] ?? 0;
        }

        $sheet = [];
        $header = ['No', 'Item Name', 'Description', 'Color', 'Size', 'Stock', 'Minimum Stock', 'Status'];
        $sheet[] = $header;

        $i = 1;
        foreach( $items as $key => $value ){
            $sheet[] = [
                $i++,
                @$value['item_name'] ?? "-",
                @$value['item_desc'] ?? "-",
                @$value['color_name'] ?? "-",
                @$value['item_size'] ?? "-",
                $stocks[$value['item_id']] ?? 0,
                $value['min_stock'],
                $stocks[$value['item_id']] ?? 0 <= $value['min_stock'] ? "need to order" : "ok",
            ];
        }

        $file_name = "Stock Report ".time();

        $xlsx = SimpleXLSXGen::fromArray( $sheet );
        $xlsx->downloadAs($file_name.".xls");

    }

}