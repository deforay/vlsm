<?php

/**
 * General functions
 *
 * @author Amit
 */
class Deforay_Commons_General {

    /**
     * Used to format date from dd-mmm-yyyy to yyyy-mm-dd for storing in database
     *
     */
    public function dateFormat($date) {
        if (!isset($date) || $date == null || $date == "" || $date == "0000-00-00") {
            return "0000-00-00";
        } else {
            $dateArray = explode('-', $date);
            if(sizeof($dateArray) == 0 ){
                return;
            }
            $newDate = $dateArray[2] . "-";

            $monthsArray = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
            $mon = 1;
            $mon += array_search(ucfirst($dateArray[1]), $monthsArray);

            if (strlen($mon) == 1) {
                $mon = "0" . $mon;
            }
            return $newDate .= $mon . "-" . $dateArray[0];
        }
    }

    public function humanDateFormat($date) {

        if ($date == null || $date == "" || $date == "0000-00-00") {
            return "";
        } else {
            $dateArray = explode('-', $date);
            $newDate = $dateArray[2] . "-";

            $monthsArray = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
            $mon = $monthsArray[$dateArray[1] - 1];

            return $newDate .= $mon . "-" . $dateArray[0];
        }
    }

    public function getZendDateFormat($date) {

        if ($date == null || $date == "" || $date == "0000-00-00") {
            return "";
        } else {
            $dateArray = explode('-', $date);

            $newDate = new Zend_date(array('year' => $dateArray[0], 'month' => $dateArray[1], 'day' => $dateArray[2]));

            return $newDate;
        }
    }
    
    public static function getDateTime($timezone = 'Asia/Calcutta') {
        $date = new DateTime( date('Y-m-d H:i:s'), new DateTimeZone($timezone));
        return $date->format('Y-m-d H:i:s');
    }
}

