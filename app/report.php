<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class report extends Model
{

  public function apikey()
  {
    $result = array(
      "dropbox" => "xjqKpNY_c1AAAAAAAAAHRHHMKntdkLcK4miq6t2zl9t1EDkZ4XhQb_HUx86nkvsK",
    );
    return $result;
  }

  public function test()
  {
    $report_object = new report;
    $path = "/1";

    $result = $report_object->test_helper_2($path, "", $report_object);

    return $result;
  }

  public function test_helper_2($path, $called, $report_object)
  {
    $result = $report_object->get_from_dropbox($path, $report_object, "files/list_folder");
    if (isset($result["entries"])) {
      $result = $result["entries"];

      $called = "";

      if (isset($result)) {
        foreach ($result as $key => $entry) {
          if ($entry['.tag'] == "folder") {
            $result[$key]["child_content"] = $report_object->test_helper_2($entry['path_display'], $called, $report_object);
          } else {
            // code...
            $file_content = $report_object->stream_from_dropbox($path, $report_object);
            $result[$key]["child_content"] = $file_content;
          }
        }
      }
    }


    return $result;
  }



  public function get_from_dropbox($path, $report_object, $url_suffix)
  {
    $report_object = new report;
    $body = array(
      "path" => $path,
    );
    $body = json_encode($body);

    // $url_suffix = "files/get_metadata";

    $apikey = $report_object->apikey()["dropbox"];
    $endpoint = 'https://api.dropboxapi.com/2/'.$url_suffix;


    $result = $report_object->curl_post($body,$endpoint,$apikey);

    $result = json_decode($result, true);
    return $result;
  }

  public function stream_from_dropbox($path, $report_object)
  {
    $apikey = $report_object->apikey()["dropbox"];

    $text = array(
      "path"=> $path,
      "recursive"=> false,
      "include_media_info"=> false,
      "include_deleted"=> false,
      "include_has_explicit_shared_members"=> false,
      "include_mounted_folders"=> true
    );
    $text = json_encode($text);

    // echo $text . '<br>' ;

    $postdata = http_build_query(
      array(
        'data' => $text
      )
    );

    $opts = array(
      'http' => array(
        'method'  => 'POST',
        'header'  => array('Authorization: Bearer ' . $apikey,
        'Content-Type: application/json'),
        'content' => $postdata
      ),
      'ssl' => array(
        "verify_peer"=>false,
        "verify_peer_name"=>false
      )
    );

    $context  = stream_context_create($opts);

    $endpoint = 'https://api.dropboxapi.com/2/files/download';

    $result = file_get_contents($endpoint, false, $context);

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
