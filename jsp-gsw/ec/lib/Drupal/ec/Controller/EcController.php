<?php

namespace Drupal\ec\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EcController extends ControllerBase {

  public function front(Request $request) {
    return theme('front_page'); 
  }

  /**
   * page callback: verify/code
   */
  public function verifyCodeImage(Request $request) {
    module_load_include('pages.inc', 'ec');

    $check_number = ec_generate_code();
    $_SESSION['user_register_check_number'] = $check_number;
    return new Response();
  }


  /**
   * page callback: verify/code
   */
  public function emailVerifyCodeImage(Request $request) {
    module_load_include('pages.inc', 'ec');

    $check_number = ec_generate_code();

    $_SESSION['user_register_email_check_number'] = $check_number;

    return new Response();
  }

  /**
   * page callback: verify/code
   */
  public function userAgreement(Request $request) {
    return array('#theme' => 'user_agreement');
  }
  
  public function aboutUs_gsw(Request $request){
  
    return array('#theme'=>'aboutUs_gsw');
  }
  
  public function aboutUs_contact(Request $request){
  
    return array('#theme'=>'aboutUs_contact');
  }
  
  public function aboutUs_store(Request $request){
  
    return array('#theme'=>'aboutUs_store');
  }

  public function aboutUs_help(Request $request){
  
    return array('#theme'=>'aboutUs_help');
  }

  public function phone(Request $request){
  
    return array('#theme'=>'phone');
  }  
}
