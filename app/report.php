<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class report extends Model
{

  public function apikey()
  {
    $result = array(
      "dropbox_token" => "xjqKpNY_c1AAAAAAAAAHV333ZDoip23UiMuNIdHL4vLhaz6uYj6oJLNbOWXC81ZA",
      "dropbox_userpwd" => array(
        "username" => "z3o9nmtmd0ikqf4",
        "password" => "ntibchtud5z4lmr",
      ),
    );
    return $result;
  }

  public function dropbox_files_recursive()
  {
    $report_object = new report;
    $path = "";

    $result = $report_object->dropbox_files_recursive_helper($path, "", $report_object);


    return $result;
  }

  public function dropbox_files_recursive_helper($path, $called, $report_object)
  {
    $result = $report_object->get_from_dropbox($path, $report_object, "files/list_folder");
    $result_2 = array();

    if (isset($result["entries"])) {
      $result = $result["entries"];

      $called = "";

      if (isset($result)) {
        foreach ($result as $key => $entry) {

          if ($entry['.tag'] == "folder") {
            $sub_result_sum = $report_object->dropbox_files_recursive_helper($entry['path_display'], $called, $report_object);
            $result[$key]["child_content"] = $sub_result_sum["all_nested"];
          } else {
            if (strtotime($entry["server_modified"]) > strtotime("-1 minutes")) {
              $result_2[] = $entry;
            }
            $result[$key]["child_content"] = "";
            // $result[$key]["child_content"] = $report_object->file_contents($entry['path_display'], $report_object);
          }
        }
      }
    }
    $result_sum = array(
      "all_nested" => $result,
      "uncached" => $result_2,
    );
    return $result_sum;
  }

  public function file_contents($path, $report_object)
  {
    $file_content = $report_object->get_from_dropbox($path, $report_object, "files/get_temporary_link");
    if (isset($file_content["link"])){
      $file_content = $file_content["link"];
      $file_content = file_get_contents($file_content);
    } else {
      $file_content = "";
    }
    $result = $file_content;

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

    $userpwd = "";
    // $userpwd = $report_object->apikey()["dropbox_userpwd"];

    $token = "";
    $token = $report_object->apikey()["dropbox_token"];

    $endpoint = 'https://api.dropboxapi.com/2/'.$url_suffix;


    $result = $report_object->curl_post($body,$endpoint,$userpwd,$token);

    $result = json_decode($result, true);
    return $result;
  }

  public function curl_post($body, $endpoint, $userpwd, $token)
  {

    $ch = curl_init();

    // set URL and other appropriate options
    $options = array(
      CURLOPT_URL => $endpoint,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => $body,
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
      ),
    );
    if (!empty($userpwd)) {
      $options[CURLOPT_USERPWD] = $userpwd['username'] . ":" . $userpwd['password'];
      // $options[CURLOPT_HTTPHEADER][] = "Authorization: Basic <base64(".$userpwd['username'].":".$userpwd['password'].")>";
    } elseif (!empty($token)) {
      $options[CURLOPT_HTTPHEADER][] = "Authorization: Bearer $token";
    }

    // dd($options);

    curl_setopt_array($ch, $options);



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

  public function curl_get($endpoint,$userpwd)
  {


    $ch = @curl_init();
    if (!empty($userpwd)) {
      curl_setopt($ch, CURLOPT_USERPWD, $userpwd['username'] . ":" . $userpwd['password']);
    }

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

  public function update_cache()
  {
    $report_object = new report;

    if (isset($_GET['challenge'])) {
      $result = $_GET['challenge'];
      // echo 123;
      $report_object->log_timestamp("Challange");
      return $result;

    } elseif ($report_object->authenticate() == 1) {

      $dropbox_files_recursive = $report_object->dropbox_files_recursive();
      $uncached = array_column($dropbox_files_recursive["uncached"], "path_lower");
      $uncached = json_encode($uncached,JSON_PRETTY_PRINT);
      $report_object->log_timestamp("Authenticated".$uncached);

    } else {
      header('HTTP/1.0 403 Forbidden');
      $report_object->log_timestamp("Not authenticated");
    }



  }

  public function log_timestamp($input_string)
  {
    $timestamp = date('Y-m-d h:i:s a', time());
    $file_content =  $input_string." ".$timestamp;
    $file_name = "GTest.txt";
    file_put_contents($file_name, $file_content);
  }

  public function authenticate()
  {
    $result = 0;
    $report_object = new report;
    $userpwd = "";
    $userpwd = $report_object->apikey()["dropbox_userpwd"];
    // $token = "";
    // $token = $report_object->apikey()["dropbox_token"];

    $raw_data = file_get_contents('php://input');
    if ($raw_data) {
      $json = json_decode($raw_data);
      if (is_object($json)) {
        if (isset($json->list_folder)) {
          $headers = $report_object->getallheaders();
          if (hash_hmac("sha256", $raw_data, $userpwd['password']) == $headers['X-Dropbox-Signature']) {
            $result = 1;
          }
        }
      }
    }
    return $result;
  }

  function getallheaders()  {
    $headers = array();
    foreach ($_SERVER as $name => $value)  {
      if (substr($name, 0, 5) == 'HTTP_') {

        $name = substr($name, 5);
        $name = str_replace('_', ' ', $name);
        $name = strtolower($name);
        $name = ucwords($name);
        $name = str_replace(' ', '-', $name);

        $headers[$name] = $value;

      }
    }
    return $headers;
  }



}
