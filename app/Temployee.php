<?php

namespace App;

use Elasticquent\ElasticquentTrait;

class Temployee extends \Eloquent {
  use ElasticquentTrait;
    protected $table='t_employee';
    // protected $timestamps=false;
     public $fillable = ['EMPLOYEE_ID', 'SURNAME', 'NAME', 'DOB', 'DOJ', 'DOL', 'EMP_ID', 'USER_NAME', 'PASS_WORD', 'FMS_LOGIN', 'IP_ADDRESS', 'MAC_ADDRESS', 'LAST_LOGIN_TIME', 'MOBILENO', 'PAYROLL_ID', 'STATUS', 'CAMPUS_ID', 'ORG_ID', 'DESIGNATION', 'ROLE_ID', 'CREATED_ON', 'CREATED_BY', 'MOBILE', 'UUID', 'CASH_AMOUNT', 'DD_AMOUNT', 'CHEQUE_AMOUNT', 'SUBJECT', 'EMP_TYPE', 'IMAGE_PATH', 'ESI_ELIGIBLE', 'PF_ELIGIBLE', 'EMP_TRANSFER_STATUS', 'EMP_TRANSFER_CAMPUS', 'PRINTER_TYPE', 'REPORTER_ID', 'ALLOWED_CONCESSION', 'IS_VALID', 'GENDER', 'PAN', 'IS_COLLEGE', 'IS_DGM', 'ACCOUNT_NO', 'BANK_NAME', 'MOBILE_DEVICE_ID', 'ACCESS_STATUS', 'MANAGER_ID', 'PWD_STATUS', 'EMP_HISTORY_ID', 'INCENTIVE', 'COMPANY', 'PAYROLL_CAMPUS_ID', 'SAL_RANGE', 'PF_NUMBER', 'BUILDING_ID', 'REGISTRATION_TYPE', 'fcm_id', 'APP_ROLE_ID','SKIP_ATTENDANCE'];
  protected $mappingProperties = array(
    'EMPLOYEE_ID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'SURNAME' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'NAME' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'DOB' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'DOJ' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'DOL' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'EMP_ID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'USER_NAME' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'PASS_WORD' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'FMS_LOGIN' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'IP_ADDRESS' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'MAC_ADDRESS' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'LAST_LOGIN_TIME' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'MOBILENO' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'PAYROLL_ID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'STATUS' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'CAMPUS_ID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'ORG_ID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'DESIGNATION' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'ROLE_ID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'CREATED_ON' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'CREATED_BY' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'MOBILE' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'UUID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'CASH_AMOUNT' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'DD_AMOUNT' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'CHEQUE_AMOUNT' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'SUBJECT' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'EMP_TYPE' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'IMAGE_PATH' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'ESI_ELIGIBLE' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'PF_ELIGIBLE' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'EMP_TRANSFER_STATUS' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'EMP_TRANSFER_CAMPUS' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'PRINTER_TYPE' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'REPORTER_ID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'ALLOWED_CONCESSION' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'IS_VALID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'GENDER' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'PAN' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'IS_COLLEGE' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'IS_DGM' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'ACCOUNT_NO' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'BANK_NAME' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'MOBILE_DEVICE_ID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'ACCESS_STATUS' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'MANAGER_ID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'PWD_STATUS' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'EMP_HISTORY_ID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'INCENTIVE' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'COMPANY' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'PAYROLL_CAMPUS_ID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'SAL_RANGE' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'PF_NUMBER' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'BUILDING_ID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'REGISTRATION_TYPE' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'fcm_id' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'APP_ROLE_ID' => [
      'type' => 'string',
      "analyzer" => "standard",
    ],
    'SKIP_ATTENDANCE' => [
      'type' => 'string',
      "analyzer" => "stop",
      "stopwords" => [","]
    ],
  );
}
