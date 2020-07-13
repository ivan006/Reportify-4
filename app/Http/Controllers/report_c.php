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


      $diff_level_1 = $report_object->diff_level_1($report_object);
      $diff_level_2 = $report_object->diff_level_2($report_object);

      // $var1 = json_encode($var1, JSON_PRETTY_PRINT);

      $state2 = $report_object->state($report_object);

      return view('welcome', compact("diff_level_2", "diff_level_1"));
    }

    public function update_updates_pending_log()
    {
      $report_object = new report;
      $var1 = 1;
      $var2 = $report_object->update_updates_pending_log();
      // $var2 = json_encode($var2, JSON_PRETTY_PRINT);
      return $var2;
    }

}
