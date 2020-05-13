<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class report extends Model
{

  public function apikey()
  {
    $result = array(
      "dropbox_token" => "xjqKpNY_c1AAAAAAAAAHR6868i7CtjCKxbEtfFLeH1BMLw7CrY109NtI_cFihj_U",
      "dropbox_userpwd" => array(
        "username" => "z3o9nmtmd0ikqf4",
        "password" => "ntibchtud5z4lmr",
      ),
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
            $result[$key]["child_content"] = "";
            // $result[$key]["child_content"] = $report_object->file_contents($entry['path_display'], $report_object);
          }
        }
      }
    }

    return $result;
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

  public function webhook_endpoint()
  {
    $report_object = new report;
    $result = "";
    $file_content_prefix = "";

    if (isset($_GET['challenge'])) {
      $result = $_GET['challenge'];
      // echo 123;
      $file_content_prefix = "Challange";
    } elseif ($report_object->webhook_endpoint_authenticate() == 1) {
      $file_content_prefix = "Authenticated";
    } else {
      header('HTTP/1.0 403 Forbidden');
      $file_content_prefix = "Not authenticated";
    }

    $timestamp = date('Y-m-d h:i:s a', time());
    $file_content =  $file_content_prefix." ".$timestamp;
    $file_name = "GTest.txt";
    file_put_contents($file_name, $file_content);

    return $result;
  }

  public function webhook_endpoint_authenticate()
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
          $headers = $report_object->webhook_endpoint_getallheaders();
          if (hash_hmac("sha256", $raw_data, $userpwd['password']) == $headers['X-Dropbox-Signature']) {
            $result = 1;
          }
        }
      }
    }
    return $result;
  }

  function webhook_endpoint_getallheaders()  {
    $headers = '';
    foreach ($_SERVER as $name => $value)  {
      if (substr($name, 0, 5) == 'HTTP_') {
        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
      }
    }
    return $headers;
  }



}
