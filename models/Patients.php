<?php

namespace Vlsm\Models;

/**
 * General functions
 *
 * @author Amit
 */

class Patients
{

    protected $db = null;
    protected $table = 'patients';

    public function __construct($db = null)
    {
        $this->db = !empty($db) ? $db : \MysqliDb::getInstance();
    }

    public function generatePatientId($prefix)
    {
        $this->db->where("patient_code_prefix", $prefix);
        $this->db->orderBy("patient_id", "DESC");
        $res = $this->db->getOne($this->table, array("patient_code_key"));

        if ($res) {
            $patientCodeKey = $res['patient_code_key'] + 1;
        } else {
            $patientCodeKey = 1;
        }
        $patientCode = $prefix . str_pad($patientCodeKey, 7, "0", STR_PAD_LEFT);
        return json_encode(array(
            'patientCode' => $patientCode,
            'patientCodeKey' => $patientCodeKey
        ));
    }

    public function addPatient($params)
    {
        $general = new \Vlsm\Models\General();
        $data['patient_code'] = $params['patientId'];
        $data['patient_code_key'] = $params['patientCodeKey'];
        $data['patient_code_prefix'] = $params['patientCodePrefix'];
        $data['patient_first_name'] = (!empty($params['patientFirstName']) ? $params['patientFirstName'] : null);
        $data['patient_middle_name'] = (!empty($params['patientMiddleName']) ? $params['patientMiddleName'] : null);
        $data['patient_last_name'] = (!empty($params['patientLastName']) ? $params['patientLastName'] : null);
        $data['patient_province'] = (!empty($params['patientProvince']) ? $params['patientProvince'] : null);
        $data['patient_district'] = (!empty($params['patientDistrict']) ? $params['patientDistrict'] : null);
        $data['patient_gender'] = (!empty($params['patientGender']) ? $params['patientGender'] : null);
        $data['patient_registered_on'] = $general->getDateTime();
        $data['patient_registered_by'] = $params['userId'];

        return $this->db->insert($this->table, $data);
    }
}