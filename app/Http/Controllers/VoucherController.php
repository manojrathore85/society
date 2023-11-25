<?php

namespace App\Http\Controllers;

use App\Events\VoucherCreated;
use App\Http\Requests\RequestVoucher;
use App\Models\Account;
use App\Models\flate;
use App\Models\voucher;
use App\Models\VoucherDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        
        if ($request->ajax()) {
            $voucher = Voucher::query()
                ->get();
            return $datatable = DataTables::of($voucher)->make(true);
        }
        return view('voucher.list');
    }
    public function create(){

        $accounts = Account::query()
            ->select('accounts.*', 'groups.name as groupname', 'cities.name as cityname', 'groups.nature')
            ->join('groups', 'accounts.group_id', '=', 'groups.id')
            ->leftjoin('cities', 'accounts.city_id', '=', 'cities.id')
            ->get();
        $accounts = $accounts->pluck('name', 'id');
        $flates = Flate::all();
        //->put('','Please Select');
        //dd($incomeheads);
        return view('voucher.form', compact('flates', 'accounts'));
    }
    public function store(RequestVoucher $request)
    {
        //print_r($request->post());
        // $maxno = Voucher::max('no')+1;
   
       
        // echo $flates = $flates->where('id',3)->first();
        // echo "<pre>";
        // print_r($flates->owner_name);
        // echo "</pre>";
        // exit;

        // $incomeAccount->is_maintenance= true;
        // if($incomeAccount->is_maintenance= true){
        //     echo "asdf";
        // }
        //print_r($request->post());
        // exit;
        $validated = $request->validated();
        $flates = Flate::all();
        $incomeAccount = Account::findOrfail($request->input('cr_account_id'));
        
        DB::beginTransaction();
        //try {
            $insertedVoucher = Voucher::create([
                'date' => $request->post('date'),
                'no' => Voucher::max('no') + 1,
                'type' => $request->post('type'),
                'account_id' => $request->post('cr_account_id'),
                'naration' => $request->post('naration'),
                'amount' => $request->post('amount'),
                'drcr' => 'CR',
            ]);
            $totalCr = 0;
            foreach ($request->post('flate_id') as $flateid) {
                $voucherDetails[] = [
                    'voucher_id' => $insertedVoucher->id,
                    'account_id' => $request->post('dr_account_id'),
                    'flate_id' => $flateid,
                    'amount' => $itemAmount = ($incomeAccount->category == 'Maintenance' ? $this->getMaintenanceAmount($flateid, $flates): intval($request->post('amount'))),
                    'naration' => $request->post('naration'),
                    'drcr' => 'DR',
                ];
                $totalCr = $totalCr + $itemAmount;
            }
            // echo "<pre>";
            // print_r($voucherDetails);
            // echo "</pre>";
            VoucherDetail::insert($voucherDetails);
            Voucher::findOrfail($insertedVoucher->id)->update(['amount' => $totalCr]);
            DB::commit();
            $emails= ['manojrathore85@gmail.com', 'manojrathore85rnd@gmail.com'];
            event(new VoucherCreated($emails));
            return back()->with('success', 'Record save successfuly');
        //} catch (\Exception $e) {
          //  DB::rollback();
            //return back()->with('fail', 'Getting Error:'.$e);
        //}
    }
    public function voucherDetail($id){
        try {
            $voucherDetail = VoucherDetail::query()
            ->join ('flates', 'voucher_details.flate_id','=','flates.id')
            ->join ('accounts', 'voucher_details.account_id','=','accounts.id')
            ->where('voucher_id',$id)->get();
            return response()->json([
                'status' => 'success',
                'error' => false,
                'data' => $voucherDetail,                
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'success',
                'error' => false,
                'message' => 'Getting Error:'.$e,                
            ]);
        }
    }
    public function getMaintenanceAmount($flateId,$flates){
        if($flates = $flates->where('id',$flateId)->first()){
            return $flates->maintenance_amount;
        }else{
            return 0;
        }
    }
    public function destroy(Request $request){

        DB::beginTransaction();
        try {
            
            $voucher = Voucher::findOrfail($request->id);
            $voucher->delete();
            VoucherDetail::where('voucher_id', '=', $voucher->id)->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'error' => 'true',
                'message'=> 'Record deleted successfuly'
            ]);
            //code...
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'success',
                'error' => 'true',
                'message'=> 'Getting Error:'.$e
            ]);
           // return back()->with('fail','Getting Error:'.$e);
        }

    }
    
}