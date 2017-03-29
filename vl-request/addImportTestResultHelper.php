<?php
ob_start();
session_start();
include('../includes/MysqliDb.php');
include ('../includes/PHPExcel.php');
include('../General.php');
$general=new Deforay_Commons_General();
$formConfigQuery ="SELECT * from global_config where name='vl_form'";
$configResult=$db->query($formConfigQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
  $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
try {
    if(isset($_FILES['resultFile']['name']) && $_FILES['resultFile']['name'] != ''){
        $allowedExtensions = array('xls','xlsx','csv');
        $fileName = preg_replace('/[^A-Za-z0-9.]/', '-', $_FILES['resultFile']['name']);
        $fileName = str_replace(" ", "-", $fileName);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if(in_array($extension, $allowedExtensions)) {
            if(!file_exists('../temporary') && !is_dir('../temporary')) {
                mkdir('../temporary');
            }
            if(move_uploaded_file($_FILES['resultFile']['tmp_name'], '../temporary' . DIRECTORY_SEPARATOR . $fileName)) {
                $objPHPExcel = \PHPExcel_IOFactory::load('../temporary' . DIRECTORY_SEPARATOR . $fileName);
                $sheet = $objPHPExcel->getActiveSheet();
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                $count = count($sheetData);
                if($arr['vl_form'] == 2){
                  $heighestColumn = 14;
                }else if($arr['vl_form'] == 4){
                  $heighestColumn = 13; 
                }else if($arr['vl_form'] == 3){
                  $heighestColumn = 15; 
                }else{
                   $heighestColumn = 17;
                }
                for($ro = 2; $ro <= $count; $ro++) {
                    $data = array();
                    for($col = 0; $col <= $heighestColumn; $col++) {
                       $data_heading = $sheet->getCellByColumnAndRow($col, 1)->getValue();
                       $data_value = $sheet->getCellByColumnAndRow($col, $ro)->getValue();
                       if(trim($data_heading) == ''){
                           break;
                       }else{
                           if($data_heading == 'Sample'){
                              $data['sample_code'] = $data_value;
                           }else if($data_heading == 'Sample Received Date'){
                             if(trim($data_value)!= '' && $data_value!= '00-00-0000 00:00:00'){
                                  $sampleReceivingDate = explode(" ",$data_value);
                                  $data_value = $general->dateFormat($sampleReceivingDate[0])." ".$sampleReceivingDate[1];
                               }
                              $data['date_sample_received_at_testing_lab'] = $data_value;
                           }else if($data_heading == 'Result Dispatched Date'){
                             if(trim($data_value)!= '' && $data_value!= '00-00-0000 00:00:00'){
                                  $resultDispatchedDate = explode(" ",$data_value);
                                  $data_value = $general->dateFormat($resultDispatchedDate[0])." ".$resultDispatchedDate[1];
                               }
                              $data['date_results_dispatched'] = $data_value;
                           }else if($data_heading == 'Date of Viral Load Completion'){
                              if(trim($data_value)!= '' && $data_value!= '00-00-0000'){
                                $data_value = $general->dateFormat($data_value);
                              }
                             $data['date_of_completion_of_viral_load'] = $data_value;
                           }else if($data_heading == 'Reason For VL Test'){
                              $data['vl_test_reason'] = $data_value;
                           }else if($data_heading == 'VL Testing Platform'){
                              $data['vl_test_platform'] = $data_value;
                           }else if($data_heading == 'Test Method'){
                              $data['test_methods'] = $data_value;
                           }else if($data_heading == 'Sample Testing Date'){
                              if(trim($data_value)!= '' && $data_value!= '00-00-0000 00:00:00'){
                                  $sampleTestedDate = explode(" ",$data_value);
                                  $data_value = $general->dateFormat($sampleTestedDate[0])." ".$sampleTestedDate[1];
                               }
                             $data['lab_tested_date'] = $data_value;
                           }else if($data_heading == 'Log Value'){
                             $data['log_value'] = $data_value;
                           }else if($data_heading == 'Absolute Value'){
                             $data['absolute_value'] = $data_value;
                           }else if($data_heading == 'Text Value'){
                             $data['text_value'] = $data_value;
                           }else if($data_heading == 'Viral Load Result(copiesl/ml)'){
                             $data['result'] = $data_value;
                           }else if($data_heading == 'If no result'){
                             $data['rejection'] = strtolower(str_replace(' ','_',$data_value));
                           }else if($data_heading == 'Rejection Reason'){
                                $data['sample_rejection_reason'] = NULL;
                                if(trim($data_value)!= ''){
                                    $rrQuery = 'select rejection_reason_id from r_sample_rejection_reasons where rejection_reason_name = "'.$data_value.'" or rejection_reason_name = "'.strtolower($data_value).'"';
                                    $rrResult = $db->rawQuery($rrQuery);
                                    if(isset($rrResult[0]['rejection_reason_id'])){
                                       $data['sample_rejection_reason'] = $rrResult[0]['rejection_reason_id'];
                                    }else{
                                        $rrData = array(
                                                        'rejection_reason_name'=>$data_value,
                                                        'rejection_reason_status'=>'active'
                                                );
                                        $id = $db->insert('r_sample_rejection_reasons',$rrData);
                                        $data['sample_rejection_reason'] = $id;
                                    }
                                }
                           }else if($data_heading == 'Reviewed By'){
                             $data['result_reviewed_by'] = NULL;
                             if(trim($data_value)!= ''){
                                $userQuery = 'select user_id from user_details where user_name = "'.$data_value.'" or user_name = "'.strtolower($data_value).'"';
                                $userResult = $db->rawQuery($userQuery);
                                if(isset($userResult[0]['user_id'])){
                                   $data['result_reviewed_by'] = $userResult[0]['user_id'];
                                }else{
                                    $userData = array(
                                                    'user_name'=>$data_value,
                                                    'role_id'=>4,
                                                    'status'=>'active'
                                            );
                                    $id = $db->insert('user_details',$userData);
                                    $data['result_reviewed_by'] = $id;
                                }
                             }
                           }else if($data_heading == 'Reviewed Date'){
                             if(trim($data_value)!= '' && $data_value!= '00-00-0000 00:00:00'){
                                  $reviewedDate = explode(" ",$data_value);
                                  $data_value = $general->dateFormat($reviewedDate[0])." ".$reviewedDate[1];
                               }
                              $data['result_reviewed_date'] = $data_value;
                           }else if($data_heading == 'Approved By'){
                             $data['result_approved_by'] = NULL;
                             if(trim($data_value)!= ''){
                                $userQuery = 'select user_id from user_details where user_name = "'.$data_value.'" or user_name = "'.strtolower($data_value).'"';
                                $userResult = $db->rawQuery($userQuery);
                                if(isset($userResult[0]['user_id'])){
                                   $data['result_approved_by'] = $userResult[0]['user_id'];
                                }else{
                                    $userData = array(
                                                    'user_name'=>$data_value,
                                                    'role_id'=>4,
                                                    'status'=>'active'
                                            );
                                    $id = $db->insert('user_details',$userData);
                                    $data['result_approved_by'] = $id;
                                }
                             }
                           }else if($data_heading == 'Laboratory Scientist Comments'){
                             $data['comments'] = $data_value;
                           }else if($data_heading == 'Specimen type'){
                              $data['sample_id'] = NULL;
                              if(trim($data_value)!= ''){
                                $specimenTypeQuery = 'select sample_id from r_sample_type where sample_name = "'.$data_value.'"';
                                $specimenResult = $db->rawQuery($specimenTypeQuery);
                                if(isset($specimenResult[0]['sample_id'])){
                                   $data['sample_id'] = $specimenResult[0]['sample_id'];
                                }else{
                                   $sampleTypeData = array(
                                                     'sample_name'=>$data_value,
                                                     'status'=>'active'
                                                 );
                                   $id = $db->insert('r_sample_type',$sampleTypeData);
                                   $data['sample_id'] = $id;
                                }
                              }
                           }else if($data_heading == 'Lab'){
                             $data['lab_name'] = $data_value;
                           }else if($data_heading == 'Lab Name'){
                              $data['lab_id'] = NULL;
                              if(trim($data_value)!= ''){
                                $labQuery = 'select facility_id from facility_details where facility_name = "'.$data_value.'"';
                                $labResult = $db->rawQuery($labQuery);
                                if(isset($labResult[0]['facility_id'])){
                                   $data['lab_id'] = $labResult[0]['facility_id'];
                                }else{
                                   $labData = array(
                                                    'facility_name'=>$data_value,
                                                    'facility_type'=>2,
                                                    'status'=>'active'
                                                 );
                                   $id = $db->insert('facility_details',$labData);
                                   $data['lab_id'] = $id;
                                }
                              }
                           }else if($data_heading == 'LAB No'){
                               $data['lab_no'] = $data_value;
                           }else if($data_heading == 'Lab Contact Person'){
                               $data['lab_contact_person'] = $data_value;
                           }else if($data_heading == 'Lab Phone No'){
                               $data['lab_phone_no'] = $data_value;
                           }else if($data_heading == 'Status'){
                              $data['status'] = NULL;
                              if(trim($data_value)!= ''){
                                $statusQuery = 'select status_id from r_testing_status where status_name = "'.$data_value.'" OR status_name = "'.strtolower($data_value).'"';
                                $statusResult = $db->rawQuery($statusQuery);
                                if(isset($statusResult[0]['status_id'])){
                                   $data['status'] = $statusResult[0]['status_id'];
                                }else{
                                   $tStatusData = array(
                                                     'status_name'=>$data_value
                                                 );
                                   $id = $db->insert('r_testing_status',$tStatusData);
                                   $data['status'] = $id;
                                }
                              }
                           }
                           
                           if(!isset($data['result']) || $data['result'] == ''){
                                if(isset($data['absolute_value']) && trim($data['absolute_value'])!= ''){
                                    $data['result'] = $data['absolute_value'];
                                }elseif(isset($data['log_value']) && trim($data['log_value'])!= ''){
                                    $data['result'] = $data['log_value'];
                                }
                           }
                           $data['form_id'] = $arr['vl_form'];
                           $data['modified_by'] = $_SESSION['userId'];
                           $data['modified_on'] = $general->getDateTime();
                       }
                    }
                    $sampleQuery = 'select vl_sample_id from vl_request_form where sample_code = "'.$data['sample_code'].'"';
                    $sampleResult = $db->rawQuery($sampleQuery);
                    if(isset($sampleResult[0]['vl_sample_id'])){
                        $db=$db->where('vl_sample_id',$sampleResult[0]['vl_sample_id']);
                        $db->update('vl_request_form',$data);
                    }else{
                        $data['created_by'] = $_SESSION['userId'];
                        $data['created_on'] = $general->getDateTime();
                        $db->insert('vl_request_form',$data);
                    }
                }
                $_SESSION['alertMsg']="Test Result Imported successfully";
                header("location:vlRequest.php");
            }
        }else{
            $_SESSION['alertMsg']="Invalid file format..";
            header("location:addImportTestResult.php");
        }
    }else{
         $_SESSION['alertMsg']="Unable to import..Please check all the fields";
         header("location:addImportTestResult.php");
    }
   
}catch(Exception $exc){
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}