<?php
require_once(dirname(__FILE__) . "/../startup.php");


/**
 * General functions
 *
 * @author Amit
 */

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

    public function getVLLabs()
    {
        $query = "SELECT * FROM facility_details where status='active' and facility_type=2 ORDER BY facility_name";
        return $this->db->rawQuery($query);
    }

    public function getFacilities()
    {
        $query = "SELECT * FROM facility_details where status='active' and facility_type!=2 ORDER BY facility_name";
        return $this->db->rawQuery($query);
    }
}
