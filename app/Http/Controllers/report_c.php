<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Datatables;
use App\report;

class report_c extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function state()
    {
      $report_object = new report;
      // $state = $report_object->state_raw();
      // dd($state);
      $state = $report_object->state_diff($report_object);


      $var1 = "";
      $var1 = $state;
      // $var1 = json_encode($var1, JSON_PRETTY_PRINT);

      $state2 = $report_object->state($report_object);




      return view('welcome', compact("var1", "state2"));
    }

    public function update_cache()
    {
      $report_object = new report;
      $var1 = 1;
      $var2 = $report_object->update_cache();
      // $var2 = json_encode($var2, JSON_PRETTY_PRINT);
      return $var2;
    }

}
