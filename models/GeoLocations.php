<?php

/**
 * GeoLocations functions
 *
 * @author Thana
 */

namespace Vlsm\Models;

class GeoLocations
{

    protected $db = null;

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    public function getProvinces($isApi = "no", $onlyActive = true)
    {
        return $this->fetchActiveGeolocations(null, null, $isApi, $onlyActive);
    }

    public function getDistricts($province, $isApi = "no", $onlyActive = true)
    {
        return $this->fetchActiveGeolocations(null, $province, $isApi, $onlyActive);
    }

    public function fetchActiveGeolocations($geoId = 0, $parent = '', $api = "yes", $onlyActive = true)
    {
        $returnArr = array();
        $queryParams = array();
        $whereParts = array();
        $where = '';
        if ($onlyActive) {

            $whereParts[] = " geo_status = ?";
            $queryParams[] = "active";
        }

        if (!empty($geoId)) {
            if ($geoId > 0) {
                $whereParts[] = " geo_id = ?";
                $queryParams[] = $geoId;
            }
        }
        if (!empty($parent)) {
            if (is_numeric($parent)) {
                $whereParts[] = " geo_parent = ?";
                $queryParams[] = $parent;
            } else {
                $whereParts[] = " geo_parent = 0 && geo_name = ?";
                $queryParams[] = $parent;
            }
        }else{
            $whereParts[] = " geo_parent = ?";
            $queryParams[] = 0;
        }

        if (!empty($whereParts)) {
            $where = " WHERE ";
            $where .= implode(" AND ", $whereParts);
        }

        $order = " ORDER BY geo_name ASC ";

        $response = $this->db->rawQuery("SELECT * FROM geographical_divisions " . $where.$order, $queryParams);
        if ($api == 'yes') {
            foreach ($response as $row) {
                $returnArr[$row['geo_id']] = ucwords($row['geo_name']);
            }
        } else {
            $returnArr = $response;
        }
        return $returnArr;
    }

    function addGeoLocation($geoName, $parent = 0)
    {
        $general = new \Vlsm\Models\General($this->db);

        $data = array(
            'geo_name'         => $geoName,
            'geo_status'       => 'active',
            'created_by'       => $_SESSION['userId'],
            'created_on'       => $general->getDateTime(),
            'updated_datetime' => $general->getDateTime(),
            'data_sync'       => 0
        );
        if ($parent > 0) {
            $data['geo_parent'] = $parent;
        }
        /* Check if the name already there? */
        $this->db->where("geo_name", $geoName);
        $geo = $this->db->getOne('geographical_divisions');

        /* if yes then update or else insert and return Id */
        if (isset($geo) && $geo != "") {
            $db = $this->db->where('geo_id', $geo['geo_id']);
            $db->update('geographical_divisions', $data);
            return $geo['geo_id'];
        } else {
            $this->db->insert('geographical_divisions', $data);
        }
        return $this->db->getInsertId();
    }
}
