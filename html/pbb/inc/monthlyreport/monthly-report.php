<?
$sRootPath = str_replace('\\', "/", str_replace(DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'monthlyreport', '', dirname(__FILE__)).'/');
require_once($sRootPath."inc/payment/cdatetime.php");
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/report/printer-type.php");
require_once($sRootPath."inc/report/eng-report.php");
require_once($sRootPath."inc/report/eng-report-table.php");

class MonthlyReportController {
	//members
	protected $monthlyReportEngineTable;
	protected $printerType;

	//methods
	public function __construct($printerType = null) {
		$this->monthlyReportEngineTable = null;
		$this->printerType = $printerType;
	}
	
	public function populateMonthlyReport($DBLink, $PPIDInfo, $ppid, $cid, $mid, $month, $year) {
		$monthlyData = $this->getMonthlyData($DBLink, $ppid, $cid, $mid, $month, $year);
				
		$headerValues = null;
		$bodyValues = array();
		$footerValues = null;
		
		$sumRec = 0;
		$sumBill = 0;
		$sumBillCharges = 0;
		$sumAdminCharges = 0;
		$sumTransactionAmount = 0;
		
		//bodies
		$i = 0;
		foreach ($monthlyData as $row) {
			$temp = null;
			
			//summaries
			$sumRec += intval($row["TOTAL_REC"]);
			$sumBill += intval($row["TOTAL_BILL"]);
			$sumBillCharges += intval($row["TOTAL_BILL_CHARGES"]);
			$sumAdminCharges += intval($row["TOTAL_ADMIN_CHARGES"]);
			$sumTransactionAmount += intval($row["TOTAL_TRANSACTION_AMOUNT"]);
			
			//set print values
			$temp["ROW_NO"] = ($i+1);
			$temp["ROW_DATE"] = $row["CSM_PR_DATE"];
			$temp["ROW_REC"] = $row["TOTAL_REC"];
			$temp["ROW_BILL"] = $row["TOTAL_BILL"];
			$temp["ROW_BILL_CHARGES"] = number_format($row["TOTAL_BILL_CHARGES"],2,",",".");
			$temp["ROW_ADM_CHARGES"] = number_format($row["TOTAL_ADMIN_CHARGES"],2,",",".");
			$temp["ROW_TRANSACTION_AMOUNT"] = number_format($row["TOTAL_TRANSACTION_AMOUNT"],2,",",".");
			
			$bodyValues[$i] = $temp;
			$i++;
		}

		//headers
		$headerValues["OPERATOR"] = $PPIDInfo['BANK'];
		$headerValues["PP_NAME"] = $PPIDInfo['NAMA'];
		$headerValues["PP_ID"] = $ppid;
		$headerValues["PP_ADDRESS"] = $PPIDInfo['ALAMAT'];
		$headerValues["PP_REGION"] = "";
		$headerValues["BLN"] = $month."/".$year;
		$headerValues["TGL_PRINT"] = strftime("%d/%m/%Y %H:%M:%S", time());
		$headerValues["LEMBAR_REK"] = $sumRec;
		$headerValues["LEMBAR_BLN"] = $sumBill;
		
		//footers
		$footerValues["CUSTOM_NOTES"] = "";
		$footerValues["RP_TAG"] = number_format($sumBillCharges,2,",",".");
		$footerValues["RP_ADM"] = number_format($sumAdminCharges,2,",",".");
		$footerValues["RP_REK"] = number_format($sumTransactionAmount,2,",",".");
		$footerValues["RP_TERBILANG"] = SayInIndonesian($sumTransactionAmount)." rupiah";
		
		//template
		$sTemplateFile = str_replace(DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'monthlyreport', '', dirname(__FILE__)).DIRECTORY_SEPARATOR."inc".DIRECTORY_SEPARATOR."report".DIRECTORY_SEPARATOR."template".DIRECTORY_SEPARATOR."monthlyreport".DIRECTORY_SEPARATOR."monthly-report.xml";
		$this->monthlyReportEngineTable = new ReportEngineTable($sTemplateFile, $headerValues, $bodyValues, $footerValues, $this->printerType);
	}
	
	public function printRawMonthlyReport() {
		if ($this->monthlyReportEngineTable != null) {
			$strTXT = "";
			if ($this->monthlyReportEngineTable->Print2TXT($strTXT)) {
				return $strTXT;
			} else {
				return "";
			}
		} else {
			return "";
		}
	}
	
	public function printHTMLMonthlyReport() {
		if ($this->monthlyReportEngineTable != null) {
			$strHTML = "";
			if ($this->monthlyReportEngineTable->PrintHTML($strHTML)) {
				return $strHTML;
			} else {
				return "";
			}
		} else {
			return "";
		}
	}

	public function getMonthlyData($DBLink, $ppid, $cid, $mid, $month, $year) {
		$retval = array();
		$query = sprintf(
			"SELECT 
				CSM_PR_DATE,
				Sum(CSM_PR_NREC) AS TOTAL_REC, 
				Sum(CSM_PR_NBILL) AS TOTAL_BILL, 
				Sum(CSM_PR_BILL) AS TOTAL_BILL_CHARGES, 
				Sum(CSM_PR_ADM) AS TOTAL_ADMIN_CHARGES, 
				Sum(CSM_PR_TOTAL) AS TOTAL_TRANSACTION_AMOUNT
			FROM CSCMOD_PP_REPORT 
			WHERE CSM_PR_PPID = '%s' AND 
				CSM_PR_CID = '%s' AND
				CSM_PR_MID = '%s' AND
				EXTRACT(YEAR_MONTH FROM CSM_PR_DATE) = '%s'
			GROUP BY CSM_PR_DATE",
			$ppid,
			$cid,
			$mid,
			$year.$month
		);
		$result = mysqli_query($DBLink, $query);
		while ($row = mysqli_fetch_assoc($result)) {
			$retval[] = $row;
		}
		mysqli_free_result($result);
		
		return $retval;
	}
}
?>