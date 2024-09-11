<?php
function GetIndonesianDayOfWeekLong($iWDay)
{
  $s = '';
  switch ($iWDay)
  {
    case 0: $s = 'Minggu'; break;
    case 1: $s = 'Senin'; break;
    case 2: $s = 'Selasa'; break;
    case 3: $s = 'Rabu'; break;
    case 4: $s = 'Kamis'; break;
    case 5: $s = 'Jumat'; break;
    case 6: $s = 'Sabtu'; break;
  }

  return $s;
} // end of GetIndonesianDayOfWeekLong

function GetIndonesianDayOfWeekShort($iWDay)
{
  $s = '';
  switch ($iWDay)
  {
    case 0: $s = 'Min'; break;
    case 1: $s = 'Sen'; break;
    case 2: $s = 'Sel'; break;
    case 3: $s = 'Rab'; break;
    case 4: $s = 'Kam'; break;
    case 5: $s = 'Jum'; break;
    case 6: $s = 'Sab'; break;
  }

  return $s;
} // end of GetIndonesianDayOfWeekShort

function GetIndonesianMonthLong($iMonth)
{
  $s = '';
  switch ($iMonth)
  {
    case 0: $s = 'Januari'; break;
    case 1: $s = 'Februari'; break;
    case 2: $s = 'Maret'; break;
    case 3: $s = 'April'; break;
    case 4: $s = 'Mei'; break;
    case 5: $s = 'Juni'; break;
    case 6: $s = 'Juli'; break;
    case 7: $s = 'Agustus'; break;
    case 8: $s = 'September'; break;
    case 9: $s = 'Oktober'; break;
    case 10: $s = 'November'; break;
    case 11: $s = 'Desember'; break;
  }

  return $s;
} // end of GetIndonesianMonthLong

function GetIndonesianMonthShort($iMonth)
{
  $s = '';
  switch ($iMonth)
  {
    case 0: $s = 'Jan'; break;
    case 1: $s = 'Feb'; break;
    case 2: $s = 'Mar'; break;
    case 3: $s = 'Apr'; break;
    case 4: $s = 'Mei'; break;
    case 5: $s = 'Jun'; break;
    case 6: $s = 'Jul'; break;
    case 7: $s = 'Agu'; break;
    case 8: $s = 'Sep'; break;
    case 9: $s = 'Okt'; break;
    case 10: $s = 'Nov'; break;
    case 11: $s = 'Des'; break;
  }

  return $s;
} // end of GetIndonesianMonthShort
?>
