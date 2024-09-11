<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Format{
    function currency($value){
        $data = number_format($value,0,',','.');
        return $data. ',-';
    }
    function no_currency($value){
        $data = preg_replace('/[^0-9]/', '', $value);
        return $data;
    }
    function curr($value){
        $data = number_format($value,0,',','.');
        return $data;
    }
    public function fulldate($date){
        $day = substr($date,8,2);
        $month = substr($date, 5,2);
        $res_month = $this->monthArray($month);
        $res_year = substr($date, 0, 4);
        return $day.' '.$res_month." ".$res_year;
    }
    function monthArray($month){
        $month_array = array(
                             "01"=>"Januari",
                             "02"=>"Februari",
                             "03"=>"Maret",
                             "04"=>"April",
                             "05"=>"Mei",
                             "06"=>"Juni",
                             "07"=>"Juli",
                             "08"=>"Agustus",
                             "09"=>"September",
                             "10"=>"Oktober",
                             "11"=>"November",
                             "12"=>"Desember");
        return $month_array[$month];
    }
}