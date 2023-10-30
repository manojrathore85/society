<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestGroup;
use App\Models\group;
use Illuminate\Http\Request;
use DataTables;
class GroupController extends Controller
{
    
    public   $nature = ['1'=>'Income', '2'=>'Expences', '3'=>'Assets', '4'=>'Liability'];
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        if($request->ajax()){
            $data = group::query();
            $dtbl =  DataTables::of($data)
                ->make(true);
            return $dtbl;
        }
        return view('group.list');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $group= false;
        $groups = group::pluck('name','id');
        $nature = $this->nature;
        return view('group.groupform', compact('group','groups', 'nature'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RequestGroup $request, $id = false)
    {
        $validated = $request->validated();
        try {
            $params=[
                'name'=> $validated['name'],
                'parent_id'=>$validated['parentGroup'],
                'nature'=>$validated['nature'],
                'order'=>$validated['order'],
            ];
            group::create($params);
            return response()->json([
                'status' => 'success',
                'error' => false,
                'message' => 'Record created successfuly',
            ], 200);    
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'error'=> true,
                'message' => 'Getting Error'. $e,
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $group = group::findOrfail($id);
        $groups = group::pluck('name', 'id');
        $nature = $this->nature;
        
        return view('group.groupform', compact('group','groups', 'nature'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RequestGroup $request, $id)
    {
        $validated = $request->validated();
        try {
            $params=[
                'name'=> $validated['name'],
                'parent_id'=>$validated['parentGroup'],
                'nature'=>$validated['nature'],
                'order'=>$validated['order'],
            ];
            $group = group::findOrfail($id);
            $group->update($params);
            return response()->json([
                'status'=> 'success',
                'error' => false,
                'message' => 'Record updated successfuly'
            ],200);
        } catch (\Exception $e) {
            return response()->json([
                'status'=> 'success',
                'error' => false,
                'message' => 'Getting error'.$e
            ],500);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       try {
        $group = group::findOrfail($id);
        $group->delete();
        return response()->json([
            'status' => 'success',
            'error' => false,
            'message' => 'Record deleted successfuly',
        ]);
       } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'error' => true,
                'message' => 'Getting Error'.$e,
            ]);
       }
    }
}