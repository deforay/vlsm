<?php

namespace Vlsm\Models;

class Facilities
{

    protected $db = null;
    protected $table = 'user_details';

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

    public function getTestingLabs($condition = "status = 'active'", $onlyActive= true)
    {

        $vlfmResult = null;
        if(isset($_SESSION['userId'])){
            $vlfmQuery = "SELECT GROUP_CONCAT(DISTINCT vlfm.facility_id SEPARATOR ',') as facilityId FROM vl_user_facility_map as vlfm where vlfm.user_id='" . $_SESSION['userId'] . "'";
            $vlfmResult = $this->db->rawQueryOne($vlfmQuery);
        }
        
        if (!empty($vlfmResult) && isset($vlfmResult['facilityId'])) {
            $condition .=  " AND facility_id IN (" . $vlfmResult[0]['facilityId'] . ")";
        }        

        $query = "SELECT facility_id,facility_name FROM facility_details WHERE $condition AND facility_type=2 ORDER BY facility_name";
        $results = $this->db->rawQuery($query);
        $response = array();
        foreach ($results as $row) {
            $response[$row['facility_id']] = $row['facility_name'];
        }
        return $response;        
    }

    public function getFacilities($condition = null, $onlyActive= true)
    {
        $query = "SELECT * FROM facility_details where status='active' and facility_type!=2 ORDER BY facility_name";
        return $this->db->rawQuery($query);
    }
}
