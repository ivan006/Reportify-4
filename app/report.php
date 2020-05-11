<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class report extends Model
{

  public function apikey()
  {
    $result = array(
      "dropbox" => "xjqKpNY_c1AAAAAAAAAHQ_eaGFO2Gz5blN1hIaeeHeSLGNICWN8vZN0lcKksXlS0",
    );
    return $result;
  }

  public function test()
  {
    $report_object = new report;
    $path = "/1";



    $result = $report_object->test_helper_2($path, array(), array());

    return $result;
  }

  public function test_helper_1($path)
  {
    $report_object = new report;
    $body = array(
      "path" => $path,
    );
    $body = json_encode($body);

    // $url_suffix = "files/get_metadata";
    $url_suffix = "files/list_folder";

    $apikey = $report_object->apikey()["dropbox"];
    $endpoint = 'https://api.dropboxapi.com/2/'.$url_suffix;


    $result = $report_object->curl_post($body,$endpoint,$apikey);

    $result = json_decode($result, true);
    return $result;
  }

  public function test_helper_2($path, $datas=array(), $called=array())
  {
    $report_object = new report;
    $response = $report_object->test_helper_1($path);

    $called[] = $path;
    $result = array();

    foreach ($response['entries'] as $entry)
    {

      if ($entry['.tag'] == "folder")
      {
        if (!isset($result[$entry['path_display']]))
        {
          $result[$entry['path_display']] = $entry['path_display'];
          foreach ($result as $key => $data)
          {
            if (in_array($key, $called))
            {
              continue;
            }
            else
            {
              $report_object->test_helper_2($key, $result, $called);
            }
          }
        }
      } else {
        // code...
      }
    }


    return $result;
  }


  public function curl_post($body,$endpoint,$apikey)
  {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);



    $headers = array(
      'Authorization: Bearer '.$apikey,
      'Content-Type: application/json',
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    // echo "<pre>";
    $result = json_encode(json_decode($result, true),JSON_PRETTY_PRINT);
    // echo $result;
    return $result;



  }

  public function curl_get($endpoint,$apikey)
  {


    $ch = @curl_init();

    @curl_setopt($ch, CURLOPT_URL, $endpoint);
    @curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Accept: application/json',
      'Content-Type: application/json'
    ));
    @curl_setopt($ch, CURLOPT_HEADER, 0);
    @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = @curl_exec($ch);
    $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_errors = curl_error($ch);

    @curl_close($ch);


    $response = json_encode(json_decode($response, true),JSON_PRETTY_PRINT);
    return $response;


  }




}
