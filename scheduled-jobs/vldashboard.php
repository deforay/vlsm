<?php

include_once(__DIR__ . "/../startup.php");
include_once(APPLICATION_PATH."/includes/MysqliDb.php");
include_once(APPLICATION_PATH . '/models/General.php');
include_once(APPLICATION_PATH."/vendor/autoload.php");

$general=new General($db);

try {
    $instanceQuery="SELECT * FROM s_vlsm_instance";
    $instanceResult=$db->query($instanceQuery);
    if($instanceResult){
        $vlsmInstanceId=$instanceResult[0]['vlsm_instance_id'];
        if($instanceResult[0]['last_vldash_sync'] == '' || $instanceResult[0]['last_vldash_sync'] == null){
            $instanceUpdateOn = "";
        }else{
            $expDate=explode(" ",$instanceResult[0]['last_vldash_sync']);
            $instanceUpdateOn=$expDate[0];
        }

        
        $sQuery="SELECT vl.*,s.sample_name,s.status as sample_type_status,
                        ts.*,f.facility_name,l_f.facility_name as labName,
                        f.facility_code,f.facility_state,f.facility_district,
                        f.facility_mobile_numbers,f.address,f.facility_hub_name,
                        f.contact_person,f.report_email,f.country,f.longitude,
                        f.latitude,f.facility_type,f.status as facility_status,
                        ft.facility_type_name,lft.facility_type_name as labFacilityTypeName,
                        l_f.facility_name as labName,l_f.facility_code as labCode,
                        l_f.facility_state as labState,l_f.facility_district as labDistrict,
                        l_f.facility_mobile_numbers as labPhone,l_f.address as labAddress,
                        l_f.facility_hub_name as labHub,l_f.contact_person as labContactPerson,
                        l_f.report_email as labReportMail,l_f.country as labCountry,
                        l_f.longitude as labLongitude,l_f.latitude as labLatitude,
                        l_f.facility_type as labFacilityType,
                        l_f.status as labFacilityStatus,tr.test_reason_name,
                        tr.test_reason_status,rsrr.rejection_reason_name,
                        rsrr.rejection_reason_status 
                FROM vl_request_form as vl 
                LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id 
                LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id 
                LEFT JOIN r_sample_type as s ON s.sample_id=vl.sample_type 
                INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status 
                LEFT JOIN r_vl_test_reasons as tr ON tr.test_reason_id=vl.reason_for_vl_testing 
                LEFT JOIN facility_type as ft ON ft.facility_type_id=f.facility_type 
                LEFT JOIN facility_type as lft ON lft.facility_type_id=l_f.facility_type 
                LEFT JOIN r_sample_rejection_reasons as rsrr ON rsrr.rejection_reason_id=vl.reason_for_sample_rejection";



        $sQuery .= " WHERE sample_code is not null AND sample_code !='' ";     

        if($instanceUpdateOn != ""){
            $sQuery .= " AND DATE(vl.last_modified_datetime) >= $instanceUpdateOn"; 
        }

        //echo $instanceUpdateOn;

        // echo $sQuery;die;

        //$sQuery .= " LIMIT 1000";
        
        $rResult = $db->rawQuery($sQuery);
        
        $excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $output = array();
        $sheet = $excel->getActiveSheet();
        
        $headings = array("Sample Code","Instance ID","Gender","Age In Years","Clinic Name","Clinic Code","Clinic State","Clinic District","Clinic Phone Number","Clinic Address","Clinic HUB Name","Clinic Contact Person","Clinic Report Mail","Clinic Country","Clinic Longitude","Clinic Latitude","Clinic Status","Clinic Type","Sample Type","Sample Type Status","Sample Collection Date","LAB Name","Lab Code","Lab State","Lab District","Lab Phone Number","Lab Address","Lab HUB Name","Lab Contact Person","Lab Report Mail","Lab Country","Lab Longitude","Lab Latitude","Lab Status","Lab Type","Lab Tested Date","Log Value","Absolute Value","Text Value","Absolute Decimal Value","Result","Testing Reason","Test Reason Status","Testing Status","Sample Received Datetime","Line Of Treatment","Sample Rejected","Rejection Reason Name","Rejection Reason Status","Pregnant","Breast Feeding","Art Code","Regimen Initiated Date","ARV Adherance Percentage","Is Adherance poor","Approved Datetime","DashVL_Abs","DashVL_AnalysisResult","Current Regimen","Sample Registered Datetime");
        $colNo = 1;
    
        $styleArray = array(
            'font' => array(
                'bold' => true,
                'size' => '13',
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ),
            'borders' => array(
                'outline' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            )
        );
     
        $borderStyle = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ),
            'borders' => array(
                'outline' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ),
            )
        );
     
     
        foreach ($headings as $field => $value) {
         
         $sheet->getCellByColumnAndRow($colNo, 1)->setValueExplicit(html_entity_decode($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
         $colNo++;
         
        }
        $sheet->getStyle('A1:AN1')->applyFromArray($styleArray);
     
        foreach ($rResult as $aRow) {
            $row = array();
            if($aRow['sample_tested_datetime']=='0000-00-00 00:00:00'){
             $aRow['sample_tested_datetime'] = '';
            }
            if($aRow['sample_collection_date']=='0000-00-00 00:00:00'){
             $aRow['sample_collection_date'] = '';
            }
            if($aRow['sample_received_at_vl_lab_datetime']=='0000-00-00 00:00:00'){
             $aRow['sample_received_at_vl_lab_datetime'] = '';
            }



            $VLAnalysisResult = $aRow['result_value_absolute'];
            if ($aRow['result_value_text'] == 'Target not Detected' || $aRow['result_value_text'] == 'Target Not Detected' || strtolower($aRow['result_value_text']) == 'tnd') {
                $VLAnalysisResult = 20;
            }
            else if ($aRow['result_value_text'] == '< 20' || $aRow['result_value_text'] == '<20') {
                $VLAnalysisResult = 20;
            }
            else if ($aRow['result_value_text'] == '< 40' || $aRow['result_value_text'] == '<40') {
                $VLAnalysisResult = 40;
            }
            else if ($aRow['result_value_text'] == 'Nivel de detecÁao baixo' || $aRow['result_value_text'] == 'NÌvel de detecÁ„o baixo') {
                $VLAnalysisResult = 20;
            }
            else if ($aRow['result_value_text'] == 'Suppressed') {
                $VLAnalysisResult = 500;
            }
            else if ($aRow['result_value_text'] == 'Not Suppressed') {
                $VLAnalysisResult = 1500;
            }
            else if ($aRow['result_value_text'] == 'Negative' || $aRow['result_value_text'] == 'NEGAT') {
                $VLAnalysisResult = 20;
            }	
            else if ($aRow['result_value_text'] == 'Positive') {
                $VLAnalysisResult = 1500;
            }	
            else if ($aRow['result_value_text'] == 'Indeterminado') {
                $VLAnalysisResult = "";
            }	
        
            if ($VLAnalysisResult == 'NULL' || $VLAnalysisResult == ''){
                $DashVL_Abs = 0; 
                $DashVL_AnalysisResult ='';
            }else if ($VLAnalysisResult < 1000){
                $DashVL_AnalysisResult ='Suppressed';
                $DashVL_Abs = $VLAnalysisResult;
            }else if ($VLAnalysisResult >= 1000){
                $DashVL_AnalysisResult ='Not Suppressed';
                $DashVL_Abs = $VLAnalysisResult;
            }

            $row[] = $aRow['sample_code'];
            $row[] = $aRow['vlsm_instance_id'];
            $row[] = $aRow['patient_gender'];
            $row[] = $aRow['patient_age_in_years'];
            $row[] = ($aRow['facility_name']);
            $row[] = ($aRow['facility_code']);
            $row[] = ($aRow['facility_state']);
            $row[] = ($aRow['facility_district']);
            $row[] = ($aRow['facility_mobile_numbers']);
            $row[] = ($aRow['address']);
            $row[] = ($aRow['facility_hub_name']);
            $row[] = ($aRow['contact_person']);
            $row[] = ($aRow['report_email']);
            $row[] = ($aRow['country']);
            $row[] = ($aRow['longitude']);
            $row[] = ($aRow['latitude']);
            $row[] = ($aRow['facility_status']);
            $row[] = ($aRow['facility_type_name']);
            $row[] = $aRow['sample_name'];
            $row[] = $aRow['sample_type_status'];
            $row[] = $aRow['sample_collection_date'];
            $row[] = ($aRow['labName']);
            $row[] = ($aRow['labCode']);
            $row[] = ($aRow['labState']);
            $row[] = ($aRow['labDistrict']);
            $row[] = $aRow['labPhone'];
            $row[] = $aRow['labAddress'];
            $row[] = $aRow['labHub'];
            $row[] = ($aRow['labContactPerson']);
            $row[] = ($aRow['labReportMail']);
            $row[] = ($aRow['labCountry']);
            $row[] = ($aRow['labLongitude']);
            $row[] = ($aRow['labLatitude']);
            $row[] = ($aRow['labFacilityStatus']);
            $row[] = ($aRow['labFacilityTypeName']);
            $row[] = $aRow['sample_tested_datetime'];
            $row[] = $aRow['result_value_log'];
            $row[] = $aRow['result_value_absolute'];
            $row[] = $aRow['result_value_text'];
            $row[] = $aRow['result_value_absolute_decimal'];
            $row[] = $aRow['result'];
            $row[] = ($aRow['test_reason_name']);
            $row[] = ($aRow['test_reason_status']);
            $row[] = ($aRow['status_name']);
            $row[] = $aRow['sample_received_at_vl_lab_datetime'];
            $row[] = $aRow['line_of_treatment'];
            $row[] = $aRow['is_sample_rejected'];
            $row[] = $aRow['rejection_reason_name'];
            $row[] = $aRow['rejection_reason_status'];
            $row[] = (isset($aRow['is_patient_pregnant']) && $aRow['is_patient_pregnant'] != null && $aRow['is_patient_pregnant'] != '') ? $aRow['is_patient_pregnant'] : 'unreported';
            $row[] = (isset($aRow['is_patient_breastfeeding']) && $aRow['is_patient_breastfeeding'] != null && $aRow['is_patient_breastfeeding'] != '') ? $aRow['is_patient_breastfeeding'] : 'unreported';
            $row[] = $aRow['patient_art_no'];
            $row[] = $aRow['date_of_initiation_of_current_regimen'];
            $row[] = $aRow['arv_adherance_percentage'];
            $row[] = $aRow['is_adherance_poor'];
            $row[] = $aRow['result_approved_datetime'];
            $row[] =   $DashVL_Abs;
            $row[] =   $DashVL_AnalysisResult;
            $row[] = $aRow['current_regimen'];
            $row[] = $aRow['sample_registered_at_lab'];
            $output[] = $row;
        }
    
        $start = (count($output));
        foreach ($output as $rowNo => $rowData) {
         $colNo = 1;
         foreach ($rowData as $field => $value) {
           $rRowCount = $rowNo + 2;
           $cellName = $sheet->getCellByColumnAndRow($colNo,$rRowCount)->getColumn();
           $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
           $sheet->getStyle($cellName . $start)->applyFromArray($borderStyle);
           $sheet->getDefaultRowDimension()->setRowHeight(18);
           $sheet->getColumnDimensionByColumn($colNo)->setWidth(20);
           $sheet->getCellByColumnAndRow($colNo, $rowNo + 2)->setValueExplicit(html_entity_decode($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
           $sheet->getStyleByColumnAndRow($colNo, $rowNo + 2)->getAlignment()->setWrapText(true);
           $colNo++;
         }
        }
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel, 'Xls');
        $currentDate = date("Y-m-d-H-i-s");
        $filename = 'export-vl-result-'.$currentDate.'.xls';
        $writer->save(__DIR__ . "/../temporary". DIRECTORY_SEPARATOR . $filename);
     
        //echo $filename;
        //Excel send via API
        
        //global config
        $configQuery="SELECT `value` FROM global_config WHERE name ='vldashboard_url'";
        $configResult=$db->query($configQuery);
        $vldashboardUrl = trim($configResult[0]['value']);
    
        //Base URL
        $apiUrl=$vldashboardUrl."/api/import-viral-load";
        error_log($apiUrl);
        //$apiUrl.="/files";
        //$apiUrl.="?key_identity=XXX&key_credential=YYY";
        
        $data = [];
        $data['vlFile'] = new CURLFile(__DIR__ . "/../temporary". DIRECTORY_SEPARATOR .$filename,'application/vnd.ms-excel',$filename);
        
        $options=[
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => ['Content-Type: multipart/form-data']
        ];
        
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, $options);
        $result=curl_exec($ch);
        curl_close($ch);
        
        //var_dump($result);
        $deResult=json_decode($result,true);
        if(isset($deResult['status']) && trim($deResult['status'])=='success'){
            $data=array(
                  'last_vldash_sync'=>$general->getDateTime()
            );
            $db=$db->where('vlsm_instance_id',$vlsmInstanceId);
            $db->update('s_vlsm_instance',$data);
        }
    }
}catch (Exception $exc) {
    error_log($exc->getMessage());
    error_log($exc->getTraceAsString());
}