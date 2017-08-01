<?php

namespace App\Http\Controllers\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReturnController extends Controller
{
    public function returnStandard(Request $request){
        $re=$request->input('re');
        $type=$request->input('type');
        $msg=$request->input('msg')?$request->input('msg'):[];
        return response_treatment((int)$re,$type,$msg);
    }
}
