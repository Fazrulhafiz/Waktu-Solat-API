<?php 

/*
	Waktu Solat API v2 created by Afif Zafri
	XML data are fetch directly from JAKIM e-solat website
	This new version will be able to fetch prayer time data for the whole Year or by each month for chosen Zone
*/

# function for fetching the webpage and parse data
function fetchPage($kodzon,$tahun,$bulan)
{
	$url = "http://www.e-solat.gov.my/web/muatturun.php?zone=".$kodzon."&jenis=year&lang=en&year=".$tahun."&bulan=".$bulan;
		
	# use cURL to fetch webpage
    $ch = curl_init(); # initialize curl object
    curl_setopt($ch, CURLOPT_URL, $url); # set url
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); # receive server response
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); # do not verify SSL
    $data = curl_exec($ch); # execute curl, fetch webpage content
    echo curl_error($ch);
    $httpstatus = curl_getinfo($ch, CURLINFO_HTTP_CODE); # receive http response status
    curl_close($ch);  # close curl

    # parse the data using regex
    $patern = '#<table width=\"100%\" cellspacing=\"1\" cellpadding=\"3\" bgcolor=\"\#7C7C7C\"\>([\w\W]*?)</table>#'; 
    preg_match_all($patern, $data, $parsed);  

    $trpatern = "#<tr([\w\W]*?)</tr>#";
    preg_match_all($trpatern, implode('',$parsed[0]), $trparsed); 

    unset($trparsed[0][0]); # remove an array element because we don't need the 1st row (table heading) 
    $trparsed[0] = array_values($trparsed[0]); # rearrange the array index

    $arrData = array();
    $arrData['httpstatus'] = $httpstatus;

    if(count($trparsed[0]) > 0)
    {
        for($j=0;$j<count($trparsed[0]);$j++)
        {
            # parse the table by column <td>
            $tdpatern = "#<td([\w\W]*?)</td>#";
            preg_match_all($tdpatern, $trparsed[0][$j], $tdparsed);

            # store into variable, strip_tags is for removeing html tags
            $date = strip_tags($tdparsed[0][0]);
            $day = strip_tags($tdparsed[0][1]);
            $imsak = strip_tags($tdparsed[0][2]);
            $subuh = strip_tags($tdparsed[0][3]);
            $syuruk = strip_tags($tdparsed[0][4]);
            $zohor = strip_tags($tdparsed[0][5]);
            $asar = strip_tags($tdparsed[0][6]);
            $maghrib = strip_tags($tdparsed[0][7]);
            $isyak = strip_tags($tdparsed[0][8]);

            $arrData['data'][$j]['date'] = $date." ".$tahun;
            $arrData['data'][$j]['day'] = $day;
            $arrData['data'][$j]['imsak'] = $imsak;
            $arrData['data'][$j]['subuh'] = $subuh;
            $arrData['data'][$j]['syuruk'] = $syuruk;
            $arrData['data'][$j]['zohor'] = $zohor;
            $arrData['data'][$j]['asar'] = $asar;
            $arrData['data'][$j]['maghrib'] = $maghrib;
            $arrData['data'][$j]['isyak'] = $isyak;
        }
    }

    return $arrData;
}

# if month is chosen, then only fetch data for the chosen month
if(isset($_GET['zon']) && isset($_GET['tahun']) && isset($_GET['bulan']))
{
	$kodzon = $_GET['zon']; # store get parameter in variable
	$tahun = $_GET['tahun'];
	$bulan = $_GET['bulan'];

	$arrData = fetchPage($kodzon,$tahun,$bulan);

	# display parsed html table
	print_r($arrData['data']);
}

# if month does not chosen, fetch for all 12 months
if(isset($_GET['zon']) && isset($_GET['tahun']) && !isset($_GET['bulan']))
{
	$kodzon = $_GET['zon']; # store get parameter in variable
	$tahun = $_GET['tahun'];

	for($i=1;$i<=12;$i++)
	{

		$arrData = fetchPage($kodzon,$tahun,$i);

		# display parsed html table
		print_r($arrData);
	}
}

#### todo
# return JSON

?>