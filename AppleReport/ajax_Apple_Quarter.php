<?php

/**
* @description
* This code is used by Apple report with AngularJS service
* It builds json object & passed back to AngularJS to process
* @author Aizizi Yigaimu
* @started 2016-08-18
* @last update 2016-09-18
*/

require "common.php";

Server::$force_production = true;
setlocale(LC_MONETARY, 'en_US.UTF-8');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');

/**
* Use linkedlist to handle quarter
*/
class Quarter{

    function __construct($months, $prev=null, $last_day=30){
        $this->months = $months;
        $this->prev = $prev;
        $this->last_day = $last_day;
        $this->start_quarter_date = date('Y').'-'.$this->months[0].'-01 00:00:00';
        $this->end_quarter_date = date('Y').'-'.$this->months[2].'-'.$this->last_day.' 23:59:59';
    }

    /**
    * Create factory method to ease the object creation
    */
    static function factory($quarter){
        if($quarter=='q1')
            return new Quarter(array('01','02','03'), null, 31);
        else if($quarter=='q2')
            return new Quarter(array('04','05','06'), Quarter::factory('q1'), 30);
        else if($quarter=='q3')
            return new Quarter(array('07','08','09'), Quarter::factory('q2'), 30);
        else if($quarter=='q4')
            return new Quarter(array('10','11','12'), Quarter::factory('q3'), 31);
    }

    function getPrevious4QuartersDates($month){

        if($this->prev==null){
            $start_year = date('Y')-1;
            $this->start_date = $start_year.'-01-01 00:00:00';
            $this->end_date = $start_year.'-12-31 23:59:59';
        }else{
            $this->end_date = date('Y').'-'.array_pop($this->prev->months).'-'.$this->prev->last_day.' 23:59:59';

            $depth = 0;
            $tmp_q = $this;

            while($depth<4){
                if($tmp_q->prev==null)
                    $tmp_q = Quarter::factory('q4');
                else
                    $tmp_q = $tmp_q->prev;
                $depth += 1;
            }

            $this->start_date = (date('Y')-1).'-'.$tmp_q->months[0].'-01 00:00:00';
        }
    }

}

/**
* Report panel settings
* Global variables
*/
class Settings{

	const previous4QuarterAppleSpendLimit = 2500;
	const currentQuarterAppleSpendLimit = 5000;
	const spendDifference_1 = 1000;
	const spendDifference_2 = 2500;

	public static $appleCareCategory = array( 102060000, 102060100, 102060200, 102060300, 102060400, 102060500 );
	public static $iPadCategory = array( 102030000, 102030100, 102030101, 102030103, 102030200, 102030201, 102030203, 102030300, 102030301, 102030302 );
	public static $tags = array(906 => "BIZ CYT", 946 => "BIZ DIL", 1156 => "BIZ DRL", 1066 => "BIZ JCS", 1076 => "BIZ MDA", 916 => "BIZ TR", 1046 => "BIZ WTB");

	public static $filterOption = "Show All";  // Default all customers
	public static $company = "Tekserve";  // Default show Tekserve
	public static $company_where = "";
	public static $tag_where = "";

    public static function getEligibleQuarterAppleSpendLimit_1(){
    	return self::currentQuarterAppleSpendLimit - self::spendDifference_1;
    }

    public static function getEligibleQuarterAppleSpendLimit_2(){
    	return self::currentQuarterAppleSpendLimit - self::spendDifference_2;
    }

}

/**
* Use customer object to present data
*/
class Apple_customer{

	public $company = "";
	public $nameAttn = "";
	public $cust_id = 0;
	public $TekSalesRep = "";
	public $tag_ids = "";
	public $tag_string = "";
	public $previous4QuarterAppleSpend = 0;
	public $currentQuarterAppleSpend = 0;
	public $currentQuarterTotalSpend = 0;
	public $currentQuarterPercentAppleSpend = 0;
	public $sros = array();
	public $currentQuarterHardwareSpend = 0;
	public $currentQuarteriPadSpend = 0;
	public $currentQuarterAppleCareSpend = 0;
	public $currentQuarterOtherSpend = 0;

    public function __construct($cust_id) {
    	$this->cust_id = $cust_id;
    }

    public function getIcon(){
    	return (trim($this->company)!="") ? "customer_company_large.png" : "customer_individual_large.png";
    }

    public function getName(){
    	return (trim($this->company)=="") ? trim($this->nameAttn) : trim($this->company);
    }

    public function getColorIndicator(){
    	return ($this->currentQuarterAppleSpend >= Settings::currentQuarterAppleSpendLimit) ? "greenFont" : "redFont";
    }

	public function getTagAsString(){
		$this->tag_ids = explode(', ', implode(', ', $this->tag_ids));
		$tag_ids = array_unique($this->tag_ids);

		foreach ($tag_ids as $tag_id) {
			$criteria = new Criteria;
			$criteria->equals("tag_id",(int)$tag_id);
			$criteria->equals("hidden",0);
			$tag = DataMap::findOne("Tag",$criteria);
			if($tag) $this->tag_string .= $tag->tag_name.", ";
		}

		if( strlen($this->tag_string)>0 )
			$this->tag_string = substr($this->tag_string, 0, -2);

		return $this->tag_string;
	}

	public function getTekSalesRep( $custNumber ){
		$criteria = new Criteria;
		$criteria->equals("custNumber",$custNumber);
		$TekOpenAccountClient = DataMap::findOne("TekOpenAccountClient",$criteria);
		if($TekOpenAccountClient) $this->TekSalesRep = strtoupper($TekOpenAccountClient->TekSalesRep);
	}

	public function getCustomerAsArray(){
		return array(
						"icon" => $this->getIcon(),
						"cust_id" => $this->cust_id,
						"TekSalesRep" => $this->TekSalesRep,
						"Name" => $this->getName(),
						"tag_string" => $this->tag_string,
						"previous4QuarterAppleSpend" => money_format('%.2n', $this->previous4QuarterAppleSpend),
						"currentQuarterAppleSpend" => money_format('%.2n', $this->currentQuarterAppleSpend),
						"currentQuarterTotalSpend" => money_format('%.2n', $this->currentQuarterTotalSpend),
						"currentQuarterPercentAppleSpend" => $this->currentQuarterPercentAppleSpend,
						"sros" => $this->sros,
						"currentQuarterHardwareSpend" => money_format('%.2n', $this->currentQuarterHardwareSpend),
						"currentQuarteriPadSpend" => money_format('%.2n', $this->currentQuarteriPadSpend),
						"currentQuarterAppleCareSpend" => money_format('%.2n', $this->currentQuarterAppleCareSpend),
						"currentQuarterOtherSpend" => money_format('%.2n', $this->currentQuarterOtherSpend),
						"colorIndicator" => $this->getColorIndicator()
					);
	}

}

class Line_items{

	public $quan;
	public $ExportPN;

	function __construct($quan,$ExportPN){
		$this->quan = $quan;
		$this->ExportPN = $ExportPN;
	}
}

/**
* Main Business Logic
*/
$now = new DateTime('now');
$cur_month = $now->format('m');
$q = Quarter::factory('q4');

while($q!=null){
    if( in_array($cur_month, $q->months) ){
        $q->getPrevious4QuartersDates($cur_month);
        break;
    }else if($q->prev==null)
        exit("wrong month input!");

    $q = $q->prev;
}

// echo 'The start date is '.$q->start_date.', end date is '.$q->end_date;
// exit();

Settings::$company = ( isset($_GET['company']) ) ? $_GET['company'] : Settings::$company;
Settings::$company_where = ( Settings::$company=="T2" ) ? " AND t.T2_ID>0 " : Settings::$company_where;
Settings::$company_where = ( Settings::$company=="Tekserve" ) ? " AND t.T2_ID=0 " : Settings::$company_where;
Settings::$filterOption = ( isset($_GET['filterOption']) ) ? $_GET['filterOption'] : Settings::$filterOption;
Settings::$tag_where = ( in_array(Settings::$filterOption, array_values(Settings::$tags) ) ) ? "AND ta.tag_id=".array_search(Settings::$filterOption,Settings::$tags) : " ";

$resultArray = $customers = $downloadCustomers = array();
$grand_hardware = $grand_iPad = $grand_appleCare = 0;

$db = DB::filemaker();
$sql = "
	SELECT
		cp.cust_id AS cust_id,
		t.SROnumber,
		i.amount AS Amount,
		t.nameAttn,
		t.company,
		t.custNumber,
		t.Email,
		part_categorytree.categoryCodeForKPI,
		GROUP_CONCAT(tag_id SEPARATOR ', ') AS tag_ids
	FROM tekros t
	LEFT JOIN invoiceitems i USING (SROnumber)
	LEFT JOIN tekpartnumbers USING (ExportPN)
	LEFT JOIN part_categorymap USING (idserial)
	LEFT JOIN part_categorytree USING (category_id)
	LEFT JOIN cust_person_to_sronumber cpts USING (SROnumber)
	LEFT JOIN cust_person cp USING (person_id)
	LEFT JOIN cust_tag ta ON ta.cust_id = cp.cust_id
	WHERE (t.completedDate BETWEEN '".$q->start_date."' AND '".$q->end_date."')
	AND tekpartnumbers.manufacturer IN ('Apple', 'apple')
	".Settings::$company_where."
	AND t.Quotation=0
	AND ta.tag_id!=453
	".Settings::$tag_where."
	GROUP BY i.MYid
	ORDER BY t.nameAttn ASC
	";

$rst = $db->query($sql);
if( !$rst) throw new DatabaseException("Can't fetch view data", $db);
// echo "Total number of idserials: ".$rst->num_rows."<br><br>";
while( $item = $rst->fetch_object() ){
	if(!isset($customers[$item->cust_id])){
		$customer = new Apple_customer($item->cust_id);
		$customer->nameAttn = $item->nameAttn;
		$customer->company = $item->company;
		$customers[$item->cust_id] = $customer;
	}

	$customers[$item->cust_id]->previous4QuarterAppleSpend += $item->Amount;
	if($customers[$item->cust_id]->TekSalesRep=="") $customers[$item->cust_id]->getTekSalesRep( $item->custNumber );
	$customers[$item->cust_id]->tag_ids[] = $item->tag_ids;
}

/**
* Process customers who had no purchase in previous 4 quarters
* but purchased within current quarter
*/
$cust_id_where = (count($customers)) ? "AND cp.cust_id NOT IN (".implode(",", array_keys($customers)).")" : "";
$sql = "
	SELECT
		cp.cust_id,
		t.SROnumber,
		t.nameAttn,
		t.company,
		t.custNumber,
		i.amount AS Amount,
		t.Email,
		part_categorytree.categoryCodeForKPI,
		GROUP_CONCAT(tag_id SEPARATOR ', ') AS tag_ids
	FROM tekros t
	LEFT JOIN invoiceitems i USING (SROnumber)
	LEFT JOIN tekpartnumbers USING (ExportPN)
	LEFT JOIN part_categorymap USING (idserial)
	LEFT JOIN part_categorytree USING (category_id)
	LEFT JOIN cust_person_to_sronumber cpts USING (SROnumber)
	LEFT JOIN cust_person cp USING (person_id)
	LEFT JOIN cust_tag ta ON ta.cust_id = cp.cust_id
	WHERE t.completedDate > '".$q->end_date."'".
	$cust_id_where."
	AND tekpartnumbers.manufacturer IN ('Apple', 'apple')
	".Settings::$company_where."
	AND t.Quotation=0
	AND ta.tag_id!=453
	".Settings::$tag_where."
	GROUP BY i.MYid
	ORDER BY t.nameAttn ASC
	";

$rst = $db->query($sql);
if( !$rst) throw new DatabaseException("Can't fetch view data", $db);
while( $item = $rst->fetch_object() ){
	if(!isset($customers[$item->cust_id])){
		$customer = new Apple_customer($item->cust_id);
		$customer->nameAttn = $item->nameAttn;
		$customer->company = $item->company;
		$customers[$item->cust_id] = $customer;
	}

	if($customers[$item->cust_id]->TekSalesRep=="") $customers[$item->cust_id]->getTekSalesRep( $item->custNumber );
	$customers[$item->cust_id]->tag_ids[] = $item->tag_ids;
}

foreach ($customers as $cust_id => $customer) {
	if($customer->previous4QuarterAppleSpend<Settings::previous4QuarterAppleSpendLimit){
		$customers[$cust_id]->getTagAsString();
		buildCustomerArray( $customer );
	}
}

function buildCustomerArray( $customer ){

	global $db, $q, $resultArray;
	global $grand_hardware, $grand_iPad, $grand_appleCare;

	$sros_raw = array();

	$sql = "
		SELECT
			i.TekPartNumber,
			t.tekCompany,
			tekpartnumbers.ExportPN AS ExportPN,
			i.amount AS amount,
			i.quan,
			i.price,
			i.sronumber,
			manufacturer,
			LTRIM(t.company) AS company,
			t.nameAttn AS contact,
			t.completedDate,
			t.intakeBy,
			t.Email,
			t.phone,
			part_categorytree.categoryCodeForKPI
		FROM tekros t
		LEFT JOIN invoiceitems i USING (SROnumber)
		LEFT JOIN tekpartnumbers USING (ExportPN)
		LEFT JOIN part_categorymap USING (idserial)
		LEFT JOIN part_categorytree USING (category_id)
		LEFT JOIN cust_person_to_sronumber cpts USING (SROnumber)
		LEFT JOIN cust_person cp USING (person_id)
		WHERE t.completedDate >= '".$q->start_quarter_date."'
		AND t.Quotation=0
		".Settings::$company_where."
		AND cp.cust_id=".$customer->cust_id;

	$rst = $db->query($sql);
	if( !@$rst ) throw new DatabaseException("Can't fetch view data", $db);
	while( $item = $rst->fetch_object() ){
		if ( $item->amount!=0 || $item->price!=0 ){


			if( in_array($item->manufacturer, array("Apple", "apple")) )
				$lineitem = new Line_items($item->quan, $item->ExportPN);
			else
				$lineitem = null;

			$customer->currentQuarterTotalSpend += $item->amount;
			$Hardware = $iPad = $AppleCare = $other = 0;
			$Hardware_price = $iPad_price = $AppleCare_price = $other_price = 0;

			if( !in_array($item->categoryCodeForKPI, Settings::$appleCareCategory) && !in_array($item->categoryCodeForKPI, Settings::$iPadCategory) && in_array($item->manufacturer, array("Apple","apple")) ){
				$Hardware = $item->amount;
				$Hardware_price = $item->price;
			}else if( in_array($item->categoryCodeForKPI, Settings::$appleCareCategory) ){
				$AppleCare = $item->amount;
				$AppleCare_price = $item->price;
			}else if( in_array($item->categoryCodeForKPI, Settings::$iPadCategory) ){
				$iPad = $item->amount;
				$iPad_price = $item->price;
			}else if( $item->TekPartNumber!="CTO" ){
				$other = $item->amount;
				$other_price = $item->price;
			}

			if( !array_key_exists($item->sronumber, $sros_raw) )

				$sros_raw[$item->sronumber] = array(
														"tekCompany" => ($item->tekCompany=="") ? "Tekserve" : $item->tekCompany,
														"completedDate" => $item->completedDate,
														"intakeBy" => $item->intakeBy,
														"Contact" => $item->contact,
														"Email" => $item->Email,
														"phone" => $item->phone,
														"Hardware" => $Hardware,
														"iPad" => $iPad,
														"AppleCare" => $AppleCare,
														"other" => $other,
														"TekPartNumber" => array($item->TekPartNumber),
														"Hardware_price" => $Hardware_price,
														"iPad_price" => $iPad_price,
														"AppleCare_price" => $AppleCare_price,
														"other_price" => $other_price,
														"lineitems" => ($lineitem != null) ? array($lineitem) : array()
													);

			else{

				array_push($sros_raw[$item->sronumber]["TekPartNumber"], $item->TekPartNumber);

				if($lineitem != null) array_push($sros_raw[$item->sronumber]["lineitems"], $lineitem);

				$sros_raw[$item->sronumber] = array(
														"tekCompany" => ($item->tekCompany=="") ? "Tekserve" : $item->tekCompany,
														"completedDate" => $item->completedDate,
														"intakeBy" => $item->intakeBy,
														"Contact" => $item->contact,
														"Email" => $item->Email,
														"phone" => $item->phone,
														"Hardware" => $sros_raw[$item->sronumber]["Hardware"]+$Hardware,
														"iPad" => $sros_raw[$item->sronumber]["iPad"]+$iPad,
														"AppleCare" => $sros_raw[$item->sronumber]["AppleCare"]+$AppleCare,
														"other" => $sros_raw[$item->sronumber]["other"]+$other,
														"TekPartNumber" => $sros_raw[$item->sronumber]["TekPartNumber"],
														"Hardware_price" => $sros_raw[$item->sronumber]["Hardware_price"]+$Hardware_price,
														"iPad_price" => $sros_raw[$item->sronumber]["iPad_price"]+$iPad_price,
														"AppleCare_price" => $sros_raw[$item->sronumber]["AppleCare_price"]+$AppleCare_price,
														"other_price" => $sros_raw[$item->sronumber]["other_price"]+$other_price,
														"lineitems" => $sros_raw[$item->sronumber]["lineitems"]
													);


			}

		}
	}

	foreach ($sros_raw as $sronumber => $sro) {

		if( in_array("CTO", $sro["TekPartNumber"]) ){
			$sro["Hardware"] = $sro["Hardware_price"];
			$sro["iPad"] = $sro["iPad_price"];
			$sro["AppleCare"] = $sro["AppleCare_price"];
			$sro["other"] = $sro["other_price"];
		}

		$sro["AppleTotal"] = $sro["Hardware"] + $sro["iPad"] + $sro["AppleCare"];

		if( $sro["AppleTotal"]+$sro["other"]==0 )
			$sro["ApplePercentSpend"] = 0;
		else
			$sro["ApplePercentSpend"] = number_format(floatval($sro["AppleTotal"] / ($sro["AppleTotal"]+$sro["other"])) * 100, 2, '.', '');

		$customer->currentQuarterTotalSpend += $sro["AppleTotal"] + $sro["other"];
		$customer->currentQuarterAppleSpend += $sro["AppleTotal"];
		$customer->currentQuarterOtherSpend += $sro["other"];
		$customer->currentQuarterHardwareSpend += $sro["Hardware"];
		$customer->currentQuarteriPadSpend += $sro["iPad"];
		$customer->currentQuarterAppleCareSpend += $sro["AppleCare"];

		$sro["sronumber"] = $sronumber;
		$sro["Hardware"] = money_format('%.2n', $sro["Hardware"]);
		$sro["iPad"] = money_format('%.2n', $sro["iPad"]);
		$sro["AppleCare"] = money_format('%.2n', $sro["AppleCare"]);
		$sro["other"] = money_format('%.2n', $sro["other"]);
		$sro["AppleTotal"] = money_format('%.2n', $sro["AppleTotal"]);
		$customer->sros[] = $sro;

	}

	$customer->currentQuarterPercentAppleSpend = ( !$customer->currentQuarterTotalSpend ) ? "0" : number_format(floatval($customer->currentQuarterAppleSpend/$customer->currentQuarterTotalSpend) * 100, 2, '.', '');

	if( Settings::$filterOption=="Qualified" ){

		if( $customer->currentQuarterAppleSpend >= Settings::currentQuarterAppleSpendLimit ){

			$resultArray[] = $customer->getCustomerAsArray();
			$grand_hardware += $customer->currentQuarterHardwareSpend;
			$grand_appleCare += $customer->currentQuarterAppleCareSpend;
			$grand_iPad += $customer->currentQuarteriPadSpend;
		}

	}else if( Settings::$filterOption=="<= $2,500 to qualify" ){

		if( $customer->currentQuarterAppleSpend<Settings::currentQuarterAppleSpendLimit && $customer->currentQuarterAppleSpend>=Settings::getEligibleQuarterAppleSpendLimit_2() ){

			$resultArray[] = $customer->getCustomerAsArray();
			$grand_hardware += $customer->currentQuarterHardwareSpend;
			$grand_appleCare += $customer->currentQuarterAppleCareSpend;
			$grand_iPad += $customer->currentQuarteriPadSpend;
		}

	}else if( Settings::$filterOption=="<= $1,000 to qualify" ){

		if( $customer->currentQuarterAppleSpend<Settings::currentQuarterAppleSpendLimit && $customer->currentQuarterAppleSpend>=Settings::getEligibleQuarterAppleSpendLimit_1() ){

			$resultArray[] = $customer->getCustomerAsArray();
			$grand_hardware += $customer->currentQuarterHardwareSpend;
			$grand_appleCare += $customer->currentQuarterAppleCareSpend;
			$grand_iPad += $customer->currentQuarteriPadSpend;
		}

	}else{

		$resultArray[] = $customer->getCustomerAsArray();
		$grand_hardware += $customer->currentQuarterHardwareSpend;
		$grand_appleCare += $customer->currentQuarterAppleCareSpend;
		$grand_iPad += $customer->currentQuarteriPadSpend;

	}
}

$resultArray[] = array(
						"grand_hardware" => money_format('%.2n', $grand_hardware),
						"grand_iPad" => money_format('%.2n', $grand_iPad),
						"grand_appleCare" => money_format('%.2n', $grand_appleCare),
						"grand_apple" => money_format('%.2n', $grand_hardware + $grand_iPad + $grand_appleCare)
					);

// print("<pre>");
// print_r($resultArray);
// print("</pre>");

echo json_encode($resultArray);

?>
