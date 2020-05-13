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
    public function dropbox_files_recursive()
    {
      $report_object = new report;
      $dropbox_files_recursive = $report_object->dropbox_files_recursive();


      $var1 = "";
      // $var1 = $dropbox_files_recursive;
      $var1 = $dropbox_files_recursive["all_nested"];
      $var1 = json_encode($var1, JSON_PRETTY_PRINT);

      $var2 = "";
      $var2 = $dropbox_files_recursive["uncached"];
      $var2 = json_encode($var2, JSON_PRETTY_PRINT);

      return view('welcome', compact("var1", "var2"));
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
