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

    public function getFacilityMap($userId)
    {
        if(empty($userId)) return null;

        $this->db->where("user_id", $userId);
        return $this->db->getValue("vl_user_facility_map", "GROUP_CONCAT(DISTINCT facility_id SEPARATOR ',')");
    }

    public function getFacilities($onlyActive = true)
    {

        $condition = '';
        if ($onlyActive) {
            $condition = " `status` = 'active' ";
        }

        if (isset($_SESSION['userId'])) {
            $facilityMap = $this->getFacilityMap($_SESSION['userId']);
            if (!empty($facilityMap)) {
                $condition .=  " AND `facility_id` IN (" . $facilityMap . ")";
            }
        }

        $cols = array("facility_id", "facility_name");

        if (!empty($condition)) {
            $this->db->where($condition);
        }

        $this->db->where('facility_type != 2');
        $this->db->orderBy("facility_name", "asc");

        $results = $this->db->get($this->table, null, $cols);

        $response = array();
        foreach ($results as $row) {
            $response[$row['facility_id']] = $row['facility_name'];
        }
        return $response;
    }

    public function getTestingLabs($onlyActive = true)
    {

        $condition = '';
        if ($onlyActive) {
            $condition = " `status` = 'active' ";
        }

        if (isset($_SESSION['userId'])) {
            $facilityMap = $this->getFacilityMap($_SESSION['userId']);
            if (!empty($facilityMap)) {
                $condition .=  " AND `facility_id` IN (" . $facilityMap . ")";
            }
        }

        $cols = array("facility_id", "facility_name");

        if (!empty($condition)) {
            $this->db->where($condition);
        }

        $this->db->where('facility_type = 2');
        $this->db->orderBy("facility_name", "asc");

        $results = $this->db->get($this->table, null, $cols);

        $response = array();
        foreach ($results as $row) {
            $response[$row['facility_id']] = $row['facility_name'];
        }
        return $response;
    }
}
