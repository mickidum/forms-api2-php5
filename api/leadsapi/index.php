<?php
// if ( $_SERVER['REQUEST_METHOD']=='GET' && realpath(__FILE__) == realpath( $_SERVER['SCRIPT_FILENAME'] ) ) {
//     header( 'HTTP/1.0 403 Forbidden', TRUE, 403 );
//     die( header( 'location: /error.php' ) );
// }

// CORS uncoment below if you need 'cors support'
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE');
// header('Access-Control-Allow-Headers: X-Requested-With, content-type, Authorization, Content-Type');

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require '../../vendor/autoload.php';

$app = new \Slim\App;

$app->post('/newlead', function (Request $request, Response $response, array $args) {

  $default_settings_array = [
    'items_names' => [],
    'validation' => [
      'validate' => false,
      'validate_items' => [],
      'messages' => [
        'success' => 'Thank you for subscribing',
        'error' => 'There Error while validating'
      ]
    ],
    'mail' => [
      'send' => false,
      'to' => 'usermail@example.com',
      'from' => 'siteowner',
      'subject' => 'New message from ',
      'message_header' => 'New Subscriber Added'
    ]
  ];

  $resp_object = [
    'success' => true,
    'message' => 'OK'
  ];

  $resp_object_error = [
    'success' => false,
    'message' => 'Bad Request - a form_name_id field must be sent'
  ];

  $resp_object_error_m = [
    'success' => false,
    'message' => 'Bad Request - a recent Form fields are not equal to initial Form. For make changes in current form fields delete this form and send new or change form_name_id and use new form!!!'
  ];

  $form_list = [];
  $items = [];
  $items_names = [];

  // $items_names[] = [
  //    'name' => 'item_id',
  //    'title' => 'ID'
  //  ];

  $items['item_id'] = uniqid();

  $safe_post = array_map('test_input', $request->getParsedBody());

  if (empty($safe_post['form_name_id'])) {
    $response = $response->withJson($resp_object_error, 400, JSON_UNESCAPED_UNICODE);
    return $response;
  }

  foreach ($safe_post as $key => $value) {
    if ($key !== "form_name" and $key !== "form_name_id" and $key !== "g-recaptcha-response") {
      // $items_names[] = $key;
      $items_names[] = [
        'name' => $key,
        'title' => $key
      ];
      $items[$key] = $value;
    }
  }

  $items_names[] = [
    'name' => 'date',
    'title' => 'Date'
  ];
  $items['date'] = date("d-m-Y H:i");

  // FORM ID
  $form_name_id = sanitize_file_name($safe_post['form_name_id']);
  // FORM READABLE NAME
  $form_name = $safe_post['form_name'] ? $safe_post['form_name'] : $form_name_id;
  // JSON FILE NAME
  $form_json_file_name = 'form_reg_' . $form_name_id . '.json';
  $form_list_file_name = 'form-list.json';

  $form_list_item = [
    'form_id' => $form_name_id,
    'title' => $form_name,
    'source' => $_SERVER['HTTP_REFERER'],
    // 'last_update' => date(DATE_W3C)
    'last_update' => date("d-m-Y H:i")
  ];
  $form_list[] = $form_list_item;

  // LIST OF FORMS FILE CREATING
  if (!file_exists('../forms-list/' . $form_list_file_name)) {

    $settings_file = fopen('../forms-list/' . $form_list_file_name, 'w');
    $form_list_json = json_encode($form_list, JSON_UNESCAPED_UNICODE);
    fwrite($settings_file, $form_list_json);
    fclose($settings_file);
  } else {

    $form_list_json = file_get_contents('../forms-list/' . $form_list_file_name);
    $form_temp_array = json_decode($form_list_json, true);

    foreach ($form_temp_array as $index => $form) {
      if ($form['form_id'] == $form_name_id) {
        array_splice($form_temp_array, $index, 1);
      }
    }

    array_push($form_temp_array, $form_list_item);
    $settings_file = fopen('../forms-list/' . $form_list_file_name, 'w');
    $form_list_json = json_encode($form_temp_array, JSON_UNESCAPED_UNICODE);
    fwrite($settings_file, $form_list_json);
    fclose($settings_file);
  }

  // CREATE AND SAVE LEAD FROM FORM
  if (!file_exists('../data/' . $form_json_file_name)) {

    $json_file = fopen('../data/' . $form_json_file_name, 'w');
    $default_settings_array['items_names'] = $items_names;

    $json_file_array = [
      'form_id' => $form_name_id,
      'form_name' => $form_name,
      'settings' => $default_settings_array,
      'items' => [
        $items
      ]
    ];
    $json_encode_file = json_encode($json_file_array, JSON_UNESCAPED_UNICODE);

    fwrite($json_file, $json_encode_file);
    fclose($json_file);

    $response = $response->withJson($resp_object, 201, JSON_UNESCAPED_UNICODE);
  } else {

    $json_file_array = file_get_contents('../data/' . $form_json_file_name);

    $temp_array = json_decode($json_file_array, true);

    $temp_array['form_name'] = $form_name;

    // CHECK IF INITIAL FORM IS EQUAL TO RECENT FORM
    $fields_items_names = [];

    foreach ($temp_array['settings']['items_names'] as $value) {
      $fields_items_names[$value['name']] = $value['title'];
    }

    $fields_items_names['item_id'] = '';

    if (!keys_are_equal($fields_items_names, $items)) {
      $response = $response->withJson($resp_object_error_m, 400, JSON_UNESCAPED_UNICODE);
      return $response;
    }

    array_push($temp_array['items'], $items);

    // VALIDATION (OPTIONAL)
    $validation = $temp_array['settings']['validation'] ? $temp_array['settings']['validation'] : $default_settings_array['validation'];

    // MAIL SENDING (OPTIONAL)
    $mail_sending = $temp_array['settings']['mail'] ? $temp_array['settings']['mail'] : $default_settings_array['mail'];

    if ($validation['validate'] && count($validation['validate_items'])) {

      $validate_status = validations($validation, $items);

      if (!$validate_status['success']) {
        $response = $response->withJson($validate_status, 400, JSON_UNESCAPED_UNICODE);
        return $response;
      }
    }

    if ($mail_sending['send']) {
      $to = $mail_sending['to'];
      $headers = 'MIME-Version: 1.0' . "\r\n";
      $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
      $headers .= 'From: ' . $mail_sending['from'] . '@' . $_SERVER['HTTP_HOST'];

      $subject = $mail_sending['subject'] . ' - ' . str_replace('_', ' ', $form_name);

      $message_header = "<table style='border: solid 1px #000; padding:5px 15px;'><tr><th colspan='2'><h2><strong>" . $mail_sending['message_header'] . "</strong></h2></th></tr>";
      $message_footer = "</table>";
      $message = '';

      $items_names_last_array = json_decode($json_file_array, true);
      $names_for_email = $items_names_last_array['settings']['items_names'];

      foreach ($safe_post as $key => $value) {
        if (is_array($value)) {
          $value = implode(', ', $value);
        }

        foreach ($names_for_email as $item) {
          if ($item['name'] === $key) {
            $key = $item['title'];
          }
        }

        if ($key !== "form_name" and $key !== "form_name_id") {
          $message .= "<tr><th style='text-align: inherit; padding: 5px 10px; border-top:dotted 1px #000;border-left:dotted 1px #000;'><strong>{$key}</strong></th><td style='padding: 5px 10px; border-top:dotted 1px #000;'>{$value}</td></tr>";
        }
      }

      $message = $message_header . $message . $message_footer;

      @mail($to, $subject, $message, $headers);
    }

    $json_encode_file = json_encode($temp_array, JSON_UNESCAPED_UNICODE);

    $json_file = fopen('../data/' . $form_json_file_name, 'w');
    fwrite($json_file, $json_encode_file);
    fclose($json_file);
    $response = $response->withJson($resp_object, 201, JSON_UNESCAPED_UNICODE);
  }
  return $response;
});

// CORS
$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($req, $res, $next) {
  $response = $next($req, $res);
  return $response
    ->withHeader('Access-Control-Allow-Origin', '*')
    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($req, $res) {
  $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
  return $handler($req, $res);
});

function test_input($data)
{
  if (is_array($data)) {
    $data = array_map('test_input', $data);
    return $data;
  }
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  $data = str_replace(array("\r", "\n"), '', $data);
  return $data;
}

function sanitize_file_name($filename)
{
  $filename = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $filename);
  return $filename;
}

function validations($validation, $items)
{
  $validate_items_error = [];

  foreach ($validation['validate_items'] as $v_item) {

    foreach ($items as $key => $value) {

      if (($v_item == $key && !$value)) {
        $validate_items_error[] = $v_item;
      }
    }
  }

  if (count($validate_items_error)) {
    $validation_object = [
      'success' => false,
      'message' => $validation['messages']['error'],
      'items' => $validate_items_error
    ];
  } else {

    $validation_object = [
      'success' => true,
      'message' => $validation['messages']['success']
    ];
  }
  return $validation_object;
}

function keys_are_equal($array1, $array2)
{
  return !array_diff_key($array1, $array2) && !array_diff_key($array2, $array1);
}

$app->run();
