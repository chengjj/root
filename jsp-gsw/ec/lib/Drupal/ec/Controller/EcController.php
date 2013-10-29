<?php

namespace Drupal\ec\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

class EcController extends ControllerBase {

  public function front(Request $request) {
    return theme('front_page'); 
  }

  /**
   * page callback: verify/code
   */
  public function verifyCodeImage(Request $request) {
    module_load_include('pages.inc', 'ec');
    return ec_verify_code_page();
  }


  /**
   * page callback: verify/code
   */
  public function emailVerifyCodeImage(Request $request) {
    module_load_include('pages.inc', 'ec');
    return ec_verify_code_email_page();
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
