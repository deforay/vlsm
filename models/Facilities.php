<?php

namespace Vlsm\Models;

class Facilities
{

    protected $db = null;
    protected $table = 'facility_details';

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    // $responseType = all gives all facilities lumped in one array
    // $responseType = grouped gives all clinics and labs lumped separately
    public function getAllFacilities($responseType = 'all')
    {
        $query = "SELECT * FROM facility_details where status='active' ORDER BY facility_name";

        if ($responseType == 'all') {
            return $this->db->rawQuery($query);
        } else if ($responseType == 'grouped') {
            $resultArray = $this->db->rawQuery($query);
            $response = array();
            foreach ($resultArray as $row) {
                if ($row['facility_type'] == 2) {
                    $response['labs'][] = $row;
                } else {
                    $response['facilities'][] = $row;
                }
            }
            return $response;
        }
    }

    public function getTestingPoints($facilityId)
    {

        if (empty($facilityId)) return null;

        $response = null;
        $this->db->where("facility_id", $facilityId);
        $testingPointsJson = $this->db->getValue($this->table, 'testing_points');
        if ($testingPointsJson) {
            $response = json_decode($testingPointsJson, true);
        }
        return $response;
    }

    public function getFacilityMap($userId)
    {
        if (empty($userId)) return null;

        $this->db->where("map.user_id", $userId);
        return $this->db->getValue("vl_user_facility_map map", "GROUP_CONCAT(DISTINCT map.facility_id SEPARATOR ',')");
    }



    // $testType = vl, eid, covid19 or any other tests that might be there. 
    // Default $testType is null and returns all facilities
    // $condition = WHERE condition (for eg. "facility_state = 1")
    // $allData = true/false (false = only id and name, true = all columns)
    // $onlyActive = true/false
    public function getHealthFacilities($testType = null, $allData = false, $condition = null, $onlyActive = true)
    {

        if (!empty($_SESSION['userId'])) {
            $facilityMap = $this->getFacilityMap($_SESSION['userId']);
            if (!empty($facilityMap)) {
                $this->db->where("`facility_id` IN (" . $facilityMap . ")");
            }
        }

        if (!empty($testType)) {
            // subquery
            $healthFacilities = $this->db->subQuery();
            $healthFacilities->where("test_type like '$testType'");
            $healthFacilities->get("health_facilities", null, "facility_id");

            $this->db->where("facility_id", $healthFacilities, 'IN');
        }

        if ($onlyActive) {
            $this->db->where('status', 'active');
        }

        if (!empty($condition)) {
            $this->db->where($condition);
        }

        $this->db->orderBy("facility_name", "asc");

        if ($allData) {
            return $this->db->get("facility_details");
        } else {

            $response = array();
            $cols = array("facility_id", "facility_name");

            $results = $this->db->get("facility_details", null, $cols);

            foreach ($results as $row) {
                $response[$row['facility_id']] = $row['facility_name'];
            }
            return $response;
        }
    }


    // $testType = vl, eid, covid19 or any other tests that might be there. 
    // Default $testType is null and returns all facilities with type=2 (testing site)
    // $condition = WHERE condition (for eg. "facility_state = 1")
    // $allData = true/false (false = only id and name, true = all columns)
    // $onlyActive = true/false
    public function getTestingLabs($testType = null, $allData = false, $condition = null, $onlyActive = true)
    {

        if (!empty($_SESSION['userId'])) {
            $facilityMap = $this->getFacilityMap($_SESSION['userId']);
            if (!empty($facilityMap)) {
                $this->db->where("`facility_id` IN (" . $facilityMap . ")");
            }
        }

        if (!empty($testType)) {
            // subquery
            $testingLabs = $this->db->subQuery();
            $testingLabs->where("test_type like '$testType'");
            $testingLabs->get("testing_labs", null, "facility_id");

            $this->db->where("facility_id", $testingLabs, 'IN');
        }

        if ($onlyActive) {
            $this->db->where('status', 'active');
        }

        if (!empty($condition)) {
            $this->db->where($condition);
        }


        $this->db->where('facility_type = 2');
        $this->db->orderBy("facility_name", "asc");

        if ($allData) {
            return $this->db->get("facility_details");
        } else {
            $response = array();
            $cols = array("facility_id", "facility_name");
            $results = $this->db->get("facility_details", null, $cols);
            foreach ($results as $row) {
                $response[$row['facility_id']] = $row['facility_name'];
            }
            return $response;
        }
    }
}
