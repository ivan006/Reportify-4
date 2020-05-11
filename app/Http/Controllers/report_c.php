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
    public function test()
    {
      $report_object = new report;
      $var1 = 1;
      $var2 = $report_object->test();
      $var2 = json_encode($var2, JSON_PRETTY_PRINT);
      return view('welcome', compact("var1", "var2"));
    }

}
