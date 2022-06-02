<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Spend;
use App\Models\SpendDetail;
use App\Http\Controllers\Controller;
use App\Http\Resources\SpendResource;
use App\Http\Resources\SpendDetailResource;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class SpendController extends Controller
{
    protected $user;
 
    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $spends = Spend::select('spends.id','month','year')->with('details')->where('month',$request->month)->get();
        // $spends = Spend::selectRaw('spends.id,month,year')
        //         ->join('spend_details', 'spend_details.spend_id', '=', 'spends.id')
        //         ->where('month',$request->month)->get();
        if($spends){
            return new SpendResource(true, 'Spend retrieved successfully!',$spends);
        }
    }
    public function usermonth(Request $request)
    {
        $data = $request->only('month');
        $validator = Validator::make($data, [
            'month' => 'required',
        ]);
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        $spends = Spend::selectRaw('month,year,sum(spend_details.total) AS total_per_month')
                ->join('spend_details', 'spend_details.spend_id', '=', 'spends.id')
                ->where('month',$request->month)
                ->groupBy('month','year')->get();
        if($spends){
            return new SpendResource(true, 'User by month!',$spends);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validate data
        $data = $request->only('month', 'year');
        $validator = Validator::make($data, [
            'month' => 'required',
            'year' => 'required'
        ]);
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        $cek = Spend::where('month',$request->month)->where('year',$request->year)->count();
        if($cek >= 1){
            return response()->json([
                'success' => false,
                'message' => 'Month or Year Already Exist.'
            ], 400);
        }
        //Request is valid, create new data
        $spend = Spend::create([
            'month'   => $request->month,
            'year'    => $request->year,
            'user_id' => $this->user->id
        ]);
        if($spend){
            // insert detail
            foreach($request->details  as $key => $value){
                //Validate data
                $validator = Validator::make($value, [
                    'day' => 'required|numeric',
                    'total' => 'required|numeric'
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->messages()], 200);
                }
                $cekDetail = SpendDetail::where('spend_id',$spend->id)->where('day',$value['day'])->count();
                if($cekDetail >= 1){
                    return response()->json([
                        'success' => false,
                        'message' => 'Day Already Exist.'
                    ], 400);
                }
                $spendDetail = SpendDetail::create([
                    'spend_id'    => $spend->id,
                    'day'         => $value['day'],
                    'total'       => $value['total'],
                    'description' => $value['description']
                ]);
                if(!$spendDetail){
                    return new SpendDetailResource(false, 'Spend Detail created failed!');
                }
            }
            //Data created, return success response
            return new SpendResource(true, 'Spend created successfully!',$spend);
        }else{
            return new SpendResource(false, 'Spend created failed!');
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Spend $spend)
    {
        //Validate data
        $data = $request->only('month', 'year');
        $validator = Validator::make($data, [
            'month' => 'required',
            'year' => 'required'
        ]);
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        // $cek = Spend::where('month',$request->month)->where('year',$request->year)->where('id', '!=' , $spend->id)->count();
        // dd($cek);
        // if($cek >= 1){
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Month or Year Already Exist.'
        //     ], 400);
        // }
        $updatespend = Spend::find($spend->id);
        $updatespend->month = $request->month;
        $updatespend->year  = $request->year;
        $updatespend->user_id  = $this->user->id;
        $updatespend->save();

        if($updatespend){
            // insert detail
            $spendDetail = spendDetail::where('spend_id', $spend->id);
    		$spendDetail->delete();
            foreach($request->details  as $key => $value){
                //Validate data
                $validator = Validator::make($value, [
                    'day' => 'required|numeric',
                    'total' => 'required|numeric'
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->messages()], 200);
                }
                $spendDetail = SpendDetail::create([
                    'spend_id'    => $updatespend->id,
                    'day'         => $value['day'],
                    'total'       => $value['total'],
                    'description' => $value['description']
                ]);
                if(!$spendDetail){
                    return new SpendDetailResource(false, 'Spend Detail created failed!');
                }
            }
            //Data created, return success response
            return new SpendResource(true, 'Spend created successfully!',$updatespend);
        }else{
            return new SpendResource(false, 'Spend created failed!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Spend $spend)
    {
        $ok = $spend->delete();
        if($ok){
            return new SpendResource(true, 'Spend deleted successfully!', null);
        }else{
            return new SpendResource(false, 'Spend deleted failed!');
        }
    }
}
