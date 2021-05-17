<?php

/**
 * General functions
 *
 * @author Amit
 */

namespace Vlsm\Models;

class App
{

    protected $db = null;

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public static function generateAuthToken($length = 8, $type = 'alphanum')
    {
        // Possible seeds
        $seeds['alpha'] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwqyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwqyzABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwqyz';
        $seeds['numeric'] = '01234567890123456789012345678901234567890123456789';
        $seeds['alphanum'] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwqyz0123456789abcdefghijklmnopqrstuvwqyz0123456789abcdefghijklmnopqrstuvwqyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $seeds['hexidec'] = '0123456789abcdef';

        if (isset($seeds[$type])) {
            $keyspace = $seeds[$type];
        }

        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    public function fetchAuthToken($input)
    {
        $response['status'] = false;
        if (isset($input['authToken']) && !empty($input['authToken'])) {
            $queryParams = array($input['authToken']);
            $response['data'] = $this->db->rawQueryOne("SELECT user_id,user_name,phone_number,login_id,status FROM user_details as ud WHERE ud.api_token = ?", $queryParams);
            if ($response['data']) {
                $response['status'] = true;
            } else {
                $response['message'] = "You are not activated try to log in again";
            }
        } else {
            $response['status'] = false;
            $response['message'] = "Unauthorised access";
        }
        return $response;
    }

    public function generateSelectOptions($options)
    {
        $i = 0;
        foreach ($options as $key => $show) {
            $response[$i]['value'] = $key;
            $response[$i]['show'] = $show;
            $i++;
        }
        return $response;
    }

    public function getHealthFacilities($testType = null, $user = null, $onlyActive = false, $facilityType = 1)
    {
        $facilityDb = new \Vlsm\Models\Facilities($this->db);
        $query = "SELECT hf.test_type, f.facility_id, f.facility_name, f.facility_code, pd.province_id, pd.province_name, f.facility_district, f.facility_type 
                    from health_facilities AS hf 
                    INNER JOIN facility_details as f ON hf.facility_id=f.facility_id
                    INNER JOIN province_details as pd ON pd.province_name=f.facility_state";
        $where = "";
        if (!empty($user)) {
            $facilityMap = $facilityDb->getFacilityMap($user);
            if (!empty($facilityMap)) {
                if (isset($where) && trim($where) != "") {
                    $where .= " AND ";
                } else {
                    $where .= " WHERE ";
                }
                $where .= " facility_id IN (" . $facilityMap . ")";
            }
        }

        if (!empty($testType)) {
            if (isset($where) && trim($where) != "") {
                $where .= " AND ";
            } else {
                $where .= " WHERE ";
            }
            $where .= " hf.test_type like '$testType'";
        }

        if ($onlyActive) {
            if (isset($where) && trim($where) != "") {
                $where .= " AND ";
            } else {
                $where .= " WHERE ";
            }
            $where .= " f.status like 'active'";
        }

        if ($facilityType) {
            if (isset($where) && trim($where) != "") {
                $where .= " AND ";
            } else {
                $where .= " WHERE ";
            }
            $where .= " f.facility_type = '$facilityType'";
        }
        $where .= ' GROUP BY facility_name ORDER BY facility_name ASC';
        $query .= $where;
        $result = $this->db->rawQuery($query);
        foreach ($result as $key => $row) {
            $condition1 = " province_name like '" . $row['province_name'] . "%'";
            $condition2 = " facility_state like '" . $row['province_name'] . "%'";

            $response[$key]['value']        = $row['facility_id'];
            $response[$key]['show']         = $row['facility_name'] . ' (' . $row['facility_code'] . ')';
            /* $response[$key]['provinceId']   = $row['province_id'];
            $response[$key]['province']     = $row['province_name'];
            $response[$key]['district']     = $row['facility_district']; */
            $response[$key]['provinceDetails'] = $this->getSubFields('province_details', 'province_id', 'province_name', $condition1);
            $response[$key]['districtDetails'] = $this->getSubFields('facility_details', 'facility_district', 'facility_district', $condition2);
        }
        return $response;
    }

    public function getProvinceDetails($user = null, $onlyActive = false)
    {
        $facilityDb = new \Vlsm\Models\Facilities($this->db);
        $query = "SELECT f.facility_id, f.facility_name, f.facility_code, pd.province_id, pd.province_name, f.facility_district, f.facility_type 
                    from province_details AS pd 
                    LEFT JOIN facility_details as f ON pd.province_name=f.facility_state";
        $where = "";
        if (!empty($user)) {
            $facilityMap = $facilityDb->getFacilityMap($user);
            if (!empty($facilityMap)) {
                if (isset($where) && trim($where) != "") {
                    $where .= " AND ";
                } else {
                    $where .= " WHERE ";
                }
                $where .= " facility_id IN (" . $facilityMap . ")";
            }
        }

        if ($onlyActive) {
            if (isset($where) && trim($where) != "") {
                $where .= " AND ";
            } else {
                $where .= " WHERE ";
            }
            $where .= " f.status like 'active'";
        }

        $where .= ' GROUP BY province_name ORDER BY province_name ASC';
        $query .= $where;
        $result = $this->db->rawQuery($query);
        foreach ($result as $key => $row) {
            $condition1 = " facility_state like '" . $row['province_name'] . "%'";

            $response[$key]['value']    = $row['province_id'];
            $response[$key]['show']     = $row['province_name'];
            // $response[$key]['district'] = $row['facility_district'];
            $response[$key]['districtDetails'] = $this->getSubFields('facility_details', 'facility_district', 'facility_district', $condition1);
        }
        return $response;
    }

    public function getDistrictDetails($user = null, $onlyActive = false)
    {
        $facilityDb = new \Vlsm\Models\Facilities($this->db);
        $query = "SELECT f.facility_id, f.facility_name, f.facility_code, pd.province_id, pd.province_name, f.facility_district
                    from province_details AS pd 
                    LEFT JOIN facility_details as f ON pd.province_name=f.facility_state";
        $where = "";
        if (!empty($user)) {
            $facilityMap = $facilityDb->getFacilityMap($user);
            if (!empty($facilityMap)) {
                if (isset($where) && trim($where) != "") {
                    $where .= " AND ";
                } else {
                    $where .= " WHERE ";
                }
                $where .= " facility_id IN (" . $facilityMap . ")";
            }
        }

        if ($onlyActive) {
            if (isset($where) && trim($where) != "") {
                $where .= " AND ";
            } else {
                $where .= " WHERE ";
            }
            $where .= " f.status like 'active'";
        }

        $where .= ' GROUP BY facility_district ORDER BY facility_district ASC';
        $query .= $where;
        // die($query);
        $result = $this->db->rawQuery($query);
        foreach ($result as $key => $row) {
            $condition1 = " facility_district like '" . $row['facility_district'] . "%'";
            $condition2 = " province_name like '" . $row['province_name'] . "%'";

            $response[$key]['value']        = $row['facility_district'];
            $response[$key]['show']         = $row['facility_district'];
            $response[$key]['facilityDetails'] = $this->getSubFields('facility_details', 'facility_id', 'facility_name', $condition1);
            $response[$key]['provinceDetails'] = $this->getSubFields('province_details', 'province_id', 'province_name', $condition2);
            /* $response[$key]['facilityId']   = $row['facility_id'];
            $response[$key]['facilityName'] = $row['facility_name'].' ('.$row['facility_code'].')';
            $response[$key]['provinceId']   = $row['province_id'];
            $response[$key]['province']     = $row['province_name']; */
        }
        return $response;
    }

    public function getSubFields($tableName, $primary, $name, $condition)
    {
        $query = "SELECT $primary, $name from $tableName where $condition group by $name";
        $result = $this->db->rawQuery($query);
        $response = array();
        foreach ($result as $key => $row) {
            $response[$key]['value'] = $row[$primary];
            $response[$key]['show'] = $row[$name];
        }
        return $response;
    }

    public function fetchAllDetailsBySampleCode($sampleCode)
    {
        if (empty($sampleCode)) {
            return null;
        }
        $sQuery = "SELECT * FROM form_covid19 WHERE sample_code like '$sampleCode%' OR remote_sample_code LIKE '$sampleCode%'";
        $result =  $this->db->rawQueryOne($sQuery);
        $result['tests'] = $this->getCovid19TestsByFormId($result['covid19_id']);
        return $result;
    }

    public function getCovid19TestsByFormId($formId)
    {
        if (empty($formId)) {
            return null;
        }

        // Using this in sync requests/results
        if (is_array($formId)) {
            $sQuery = "SELECT * FROM covid19_tests WHERE `covid19_id` IN (" . implode(",", $formId) . ") ORDER BY test_id ASC";
        } else {
            $sQuery = "SELECT * FROM covid19_tests WHERE `covid19_id` = $formId ORDER BY test_id ASC";
        }
        return $this->db->rawQuery($sQuery);
    }

    public function addApiTracking($user, $records, $type, $testType, $url = null, $params = null, $format = null)
    {
        $general = new \Vlsm\Models\General($this->db);
        $data = array(
            'requested_by'          => $user,
            'requested_on'          => $general->getDateTime(),
            'number_of_records'     => $records,
            'request_type'          => $type,
            'test_type'             => $testType,
            'api_url'               => $url,
            'api_params'            => $params,
            'data_format'           => $format
        );
        return $this->db->insert("track_api_requests", $data);
    }

    public function getTableDataUsingId($tablename, $fieldName, $value)
    {
        return $this->db->rawQueryOne("SELECT * FROM " . $tablename . " WHERE " . $fieldName . " = " . $value);
    }

    public function getCovid19TestsCamelCaseByFormId($c19Id)
    {
        if (empty($c19Id)) {
            return null;
        }

        $response = array();
        $typeOfTestReqResult = array('Real Time RT-PCR', 'RDT-Antibody', 'RDT-Antigen', 'ELISA', 'Others');
        // Using this in sync requests/results
        if (is_array($c19Id)) {
            $results = $this->db->rawQuery("SELECT test_id as testId, covid19_id as covid19Id, facility_id as facilityId, test_name as testNameId, sample_tested_datetime as sampleTestedDateTime, testing_platform as testingPlatform, result FROM covid19_tests WHERE `covid19_id` IN (" . implode(",", $c19Id) . ") ORDER BY test_id ASC");

            foreach ($results as $row) {
                $response[$row['covid19_id']][$row['test_id']] = $row;
            }
        } else {
            $results = $this->db->rawQuery("SELECT test_id as testId, covid19_id as covid19Id, facility_id as facilityId, test_name as testNameId, sample_tested_datetime as sampleTestedDateTime, testing_platform as testingPlatform, result FROM covid19_tests WHERE `covid19_id` = $c19Id ORDER BY test_id ASC");
            foreach ($results as $key => $row) {
                $response[$key] = $row;
                $response[$key]['testName'] = $typeOfTestReqResult[$row['testNameId']];
            }
        }

        return $response;
    }
}
