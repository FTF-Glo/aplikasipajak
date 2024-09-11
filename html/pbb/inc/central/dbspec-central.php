<?php
class SCANCentralDbSpecific
{
	private $iDebug = 0;
	private $sLogFilename = "";
	private $DBLink = NULL;
	private $sThisFile;
	private $iErrCode = 0;
	private $sErrMsg = '';

	public function __construct($iDebug = 0, $sLogFilename, $DBLink)
	{
		$this->iDebug = $iDebug;
		$this->sLogFilename = $sLogFilename;
		$this->DBLink = $DBLink;
		$this->sThisFile = basename(__FILE__);
	}

	private function SetError($iErrCode = 0, $sErrMsg = '')
	{
		$this->iErrCode = $iErrCode;
		$this->sErrMsg = $sErrMsg;
	}

	public function GetLastError(&$iErrCode, &$sErrMsg)
	{
		$iErrCode = $this->iErrCode;
		$sErrMsg = $this->sErrMsg;
	}

	public function getDBLink()
	{
		return $this->DBLink;
	}

	// -------- GLOBAL --------- //
	public function sqlQuery($query, &$result)
	{
		$bOK = false;

		$sQ = $query;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($result = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		} else {
			echo mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	public function sqlQueryRow($query, &$res)
	{
		$bOK = false;

		$sQ = $query;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($result = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
			$nRes = mysqli_num_rows($result);
			if ($nRes > 0) {
				while ($row = mysqli_fetch_array($result)) {
					$res[] = $row;
				}
			}
		} else {
			echo mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	public function sqlQueryRows($query)
	{
		//$bOK = false;
		$nRes = 0;

		$sQ = $query;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($result = mysqli_query($this->DBLink, $sQ)) {
			//$bOK = true;
			$nRes = mysqli_num_rows($result);
		} else {
			echo mysqli_error($this->DBLink);
		}

		return $nRes;
	}

	public function sqlQueryRun($query)
	{
		//$bOK = false;
		$nRes = 0;

		$sQ = $query;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($result = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
			//$nRes = mysqli_num_rows($result);
		} else {
			echo mysqli_error($this->DBLink);
		}

		return $nRes;
	}

	/* == Untuk database Devel & Demo == */
	public function GetTerminalInfo($termId, &$terminal)
	{
		// FIX: mysql escape string
		$termId = mysqli_real_escape_string($this->DBLink, $termId);

		CTOOLS_ArrayRemoveAllElement($terminal);
		$bOK = false;

		$sQ = "SELECT * FROM csccore_central_downline WHERE CSC_CD_ID = '" . $termId . "' ";
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$row = mysqli_fetch_array($res);
				$terminal["ppid"] = $row["CSC_CD_ID"];
				$terminal["name"] = $row["CSC_CD_NAME"];
				$terminal["address"] = $row["CSC_CD_ADDRESS"];
				$terminal["phone"] = $row["CSC_CD_PHONE"];
				$bOK = true;
			}
		}

		// terminal name
		$accountNumber = $this->GetAccountNumber($termId);
		$terminal["account"] = $accountNumber;

		return $bOK;
	}

	public function GetAccountNumber($terminal)
	{
		// FIX: mysql escape string
		$terminal = mysqli_real_escape_string($this->DBLink, $terminal);

		$sQ = "SELECT CSC_PA_ACCOUNT FROM csccore_ppid_account WHERE CSC_PA_PPID = '" . $terminal . "' ";
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$row = mysqli_fetch_array($res);
				$account = $row["CSC_PA_ACCOUNT"];
				return $account;
			}
		}

		return null;
	}

	public function GetTerminalBlock($termId, &$isBlocked)
	{
		$termId = mysqli_real_escape_string($this->DBLink, $termId);

		$bOK = false;

		$sQ = "SELECT CSC_CD_ISBLOCKED FROM csccore_central_downline WHERE CSC_CD_ID = '" . $termId . "' ";
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$row = mysqli_fetch_array($res);
				$isBlocked = $row["CSC_CD_ISBLOCKED"];
				$bOK = true;
			}
		}

		return $bOK;
	}

	public function SetTerminalBlock($termId, $status)
	{
		$termId = mysqli_real_escape_string($this->DBLink, $termId);
		$status = mysqli_real_escape_string($this->DBLink, $status);

		$bOK = false;

		$sQ = "UPDATE csccore_central_downline SET CSC_CD_ISBLOCKED=$status WHERE CSC_CD_ID = '" . $termId . "' ";
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}

		return $bOK;
	}

	public function GetModuleOnpays(&$arModule)
	{
		CTOOLS_ArrayRemoveAllElement($arModule);
		$sQ = "SELECT * FROM cpccore_modules C WHERE CPC_M_ID LIKE 'm%' AND CPM_M_ISPPMODULE = 1 ORDER BY LPAD(CPC_M_ID,11,'0') ASC";
		// echo $sQ;
		$bOK = false;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$i = 0;
				$bOK = true;
				$arModule = array();
				while ($row = mysqli_fetch_array($res)) {
					$arModule[$i]["id"] = $row["CPC_M_ID"];
					$arModule[$i]["name"] = $row["CPC_M_NAME"];
					$arModule[$i]["desc"] = $row["CPC_M_DESC"];
					$arModule[$i]["varname"] = $row["CPC_M_VARNAME"];
					$arModule[$i]["version"] = $row["CPC_M_VERSION"];
					$arModule[$i]["installed"] = $row["CPC_M_INSTALLED"];
					$i++;
				}
			}
		}
		return $bOK;
	}

	/* === Message Complaint === */
	public function GetPPMessage($module = null, &$ppmessage)
	{
		$bOK = false;

		$sQCond = "";

		if ($module != null) {
			$sQCond .= "WHERE ";
			for ($i = 0; $i < count($module); $i++) {
				$module[$i] = mysqli_real_escape_string($this->DBLink, $module[$i]);
				if ($i > 0) $sQCond .= " OR ";
				$sQCond .= " CPC_PPM_MODULE='" . $module[$i] . "' ";
			}
		}

		$sQOrder = "ORDER BY CPC_PPM_SENT ASC";

		$sQ = "SELECT * FROM cpccore_payment_point_message ";
		$sQ .= $sQCond;
		$sQ .= $sQOrder;
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$ppmessage[$i] = $row;
					$i++;
				}
			}
		}

		return $bOK;
	}

	public function GetPPMessageId($id, &$ppmessage)
	{
		$id = mysqli_real_escape_string($this->DBLink, $id);

		$bOK = false;

		$sQCond = ($id != "") ? "WHERE CPC_PPM_ID='$id'" : "";

		$sQ = "SELECT * FROM cpccore_payment_point_message ";
		$sQ .= $sQCond;
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$ppmessage = mysqli_fetch_array($res);
			}
		}

		return $bOK;
	}

	public function GetPPMessageModule(&$module)
	{

		$bOK = false;

		$sQ = "SELECT CPC_PPM_MODULE FROM cpccore_payment_point_message GROUP BY CPC_PPM_MODULE ORDER BY CPC_PPM_MODULE ASC";
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$module[$i] = $row['CPC_PPM_MODULE'];
					$i++;
				}
			}
		}

		return $bOK;
	}

	public function SetPPMessageId($id, $status, $message)
	{
		$bOK = false;

		$sQ = "UPDATE cpccore_payment_point_message SET CPC_PPM_STATUS='$status', CPC_PPM_REASON='$message' WHERE CPC_PPM_ID='$id'";
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}

		return $bOK;
	}

	/* === Confirm Deposit === */
	public function GetVDeposit(&$ppmessage)
	{
		$bOK = false;

		$sQCond = "";

		$sQOrder = "ORDER BY CSC_CP_SENT_DATE ASC";

		$sQ = "SELECT * FROM csccore_confirmed_deposit ";
		$sQ .= $sQCond;
		$sQ .= $sQOrder;
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$ppmessage[$i] = $row;
					$i++;
				}
			}
		}

		return $bOK;
	}

	public function GetVDepositId($id, &$ppmessage)
	{
		$id = mysqli_real_escape_string($this->DBLink, $id);

		$bOK = false;

		$sQCond = ($id != "") ? "WHERE CSC_CP_ID='$id'" : "";

		$sQ = "SELECT * FROM csccore_confirmed_deposit ";
		$sQ .= $sQCond;
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$ppmessage = mysqli_fetch_array($res);
			}
		}

		return $bOK;
	}

	public function SetVDepositId($id, $status, $message)
	{
		$bOK = false;

		$sQ = "UPDATE csccore_confirmed_deposit SET CSC_CP_STATUS='$status', CSC_CP_REASON='$message' WHERE CSC_CP_ID='$id'";
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}

		return $bOK;
	}

	/* === Approve/Reject SMS === */
	public function GetTerminalSMS($ppid, $phoneNumber, $agentId = "")
	{
		// FIX: mysql escape string
		$ppid = mysqli_real_escape_string($this->DBLink, $ppid);
		$phoneNumber = mysqli_real_escape_string($this->DBLink, $phoneNumber);
		$agentId = mysqli_real_escape_string($this->DBLink, $agentId);

		$terminal = null;
		$sQ = "SELECT * FROM cscmod_voucher_sms_reg WHERE CSM_SR_PPID = '" . $ppid . "' AND CSM_SR_PHONE_NUMBER = '" . $phoneNumber . "' ";
		if ($agentId != "") {
			$sQ .= "AND CSM_SR_AGENT_ID = '" . $agentId . "' ";
		}
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$row = mysqli_fetch_array($res);
				$terminal = array();
				$terminal["name"] = $row["CSM_SR_NAME"];
				$terminal["bank"] = $row["CSM_SR_BANK"];
				$terminal["accountNumber"] = $row["CSM_SR_ACCOUNT_NUMBER"];
				$terminal["agentId"] = $row["CSM_SR_AGENT_ID"];
				$terminal["flag"] = $row["CSM_SR_FLAG"];
				$terminal["status"] = $row["CSM_SR_STATUS"];
				$terminal["initSetoran"] = $row["CSM_SR_INITIAL_SETORAN"];
				$terminal["initDeposit"] = $row["CSM_SR_INITIAL_DEPOSIT"];
				$terminal["pinDigest"] = $row["CSM_SR_PIN_DIGEST"];
			}
		}

		return $terminal;
	}

	public function GetTerminalSMSInfo($ppid)
	{
		// FIX: mysql escape string
		$ppid = mysqli_real_escape_string($this->DBLink, $ppid);

		$terminal = null;
		$sQ = "SELECT * FROM cscmod_voucher_sms_reg WHERE CSM_SR_PPID = '" . $ppid . "' ";
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$row = mysqli_fetch_array($res);
				$terminal = array();
				$terminal["name"] = $row["CSM_SR_NAME"];
				$terminal["bank"] = $row["CSM_SR_BANK"];
				$terminal["accountNumber"] = $row["CSM_SR_ACCOUNT_NUMBER"];
				$terminal["phoneNumber"] = $row["CSM_SR_PHONE_NUMBER"];
				$terminal["status"] = $row["CSM_SR_STATUS"];
				$terminal["initSetoran"] = $row["CSM_SR_INITIAL_SETORAN"];
				$terminal["initDeposit"] = $row["CSM_SR_INITIAL_DEPOSIT"];
				$terminal["agentId"] = $row["CSM_SR_AGENT_ID"];

				$sQ = "SELECT * FROM cscmod_voucher_sms_master_agent WHERE CSM_MA_AGENT_ID = '" . $terminal["agentId"] . "' ";
				if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
					error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
				if ($res = mysqli_query($this->DBLink, $sQ)) {
					$nRes = mysqli_num_rows($res);
					if ($nRes > 0) {
						$row = mysqli_fetch_array($res);
						$terminal["agentName"] = $row["CSM_MA_NAME"];
					} else {
						// Unknown error
						$terminal["agentName"] = "-";
					}
				}
			}
		}

		return $terminal;
	}

	public function ApproveSMS($uid, $ppid, $phoneNumber, $initDeposit, $approve, $reason = "")
	{
		// FIX: mysql escape string
		$ppid = mysqli_real_escape_string($this->DBLink, $ppid);
		$phoneNumber = mysqli_real_escape_string($this->DBLink, $phoneNumber);
		$uid = mysqli_real_escape_string($this->DBLink, $uid);
		$reason = mysqli_real_escape_string($this->DBLink, $reason);

		// CEK duplicated PPID: only for approval
		if ($approve) {
			$sQ = "SELECT COUNT(*) AS COUNT FROM cscmod_voucher_sms_reg WHERE CSM_SR_PPID = '" . $ppid . "' AND (CSM_SR_STATUS = 1 OR CSM_SR_STATUS = 3)";
			// echo $sQ;

			if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
				error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
			if ($res = mysqli_query($this->DBLink, $sQ)) {
				$nRes = mysqli_num_rows($res);
				if ($nRes > 0) {
					// terdapat PPID yang sama
					$row = mysqli_fetch_array($res);
					$count = $row["COUNT"];
					if ($count > 0) {
						return -1;
					}
				}
			}
		}

		$bOK = false;
		$approve = ($approve === true ? 1 : 2);
		$now = strftime("%Y-%m-%d %H:%M:%S", time());

		$sQ = "UPDATE cscmod_voucher_sms_reg " .
			"SET CSM_SR_STATUS = " . $approve . ", " .
			"CSM_SR_INITIAL_DEPOSIT = '" . $initDeposit . "', " .
			"CSM_SR_APPROVER_ID = '" . $uid . "', " .
			"CSM_SR_APPROVER_TIME = '" . $now . "', " .
			"CSM_SR_REASON = '" . $reason . "' " .
			"WHERE CSM_SR_PPID = '" . $ppid . "' AND CSM_SR_PHONE_NUMBER = '" . $phoneNumber . "' ";
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}

		return $bOK;
	}

	public function EditPpid($ppid, $newPpid, $phoneNumber)
	{
		// FIX: mysql escape string
		$ppid = mysqli_real_escape_string($this->DBLink, $ppid);
		$newPpid = mysqli_real_escape_string($this->DBLink, $newPpid);
		$phoneNumber = mysqli_real_escape_string($this->DBLink, $phoneNumber);

		$bOK = false;
		$sQ = "UPDATE cscmod_voucher_sms_reg " .
			"SET CSM_SR_PPID = '" . $newPpid . "' " .
			"WHERE CSM_SR_PPID = '" . $ppid . "' AND CSM_SR_PHONE_NUMBER = '" . $phoneNumber . "' ";
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}

		return $bOK;
	}
	public function EditDeposit($ppid, $phoneNumber, $deposit)
	{
		// FIX: mysql escape string
		$ppid = mysqli_real_escape_string($this->DBLink, $ppid);
		$deposit = mysqli_real_escape_string($this->DBLink, $deposit);
		$phoneNumber = mysqli_real_escape_string($this->DBLink, $phoneNumber);

		$bOK = false;
		$sQ = "UPDATE cscmod_voucher_sms_reg " .
			"SET CSM_SR_INITIAL_DEPOSIT = '" . $deposit . "' " .
			"WHERE CSM_SR_PPID = '" . $ppid . "' AND CSM_SR_PHONE_NUMBER = '" . $phoneNumber . "' ";
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}

		return $bOK;
	}

	public function SearchSMS($name, $ppid, $phoneNumber, $agentId, $status)
	{
		// FIX: mysql escape string
		$name = mysqli_real_escape_string($this->DBLink, $name);
		$ppid = mysqli_real_escape_string($this->DBLink, $ppid);
		$phoneNumber = mysqli_real_escape_string($this->DBLink, $phoneNumber);
		$agentId = mysqli_real_escape_string($this->DBLink, $agentId);

		$terminal = null;
		$first = false;
		$sQ = "SELECT * FROM cscmod_voucher_sms_reg WHERE ";
		if ($ppid != "") {
			$sQ .= "CSM_SR_PPID LIKE '%" . $ppid . "%' ";
			$first = true;
		}
		if ($phoneNumber != "") {
			if ($first) {
				$sQ .= "AND ";
			}
			$sQ .= "CSM_SR_PHONE_NUMBER LIKE '%" . $phoneNumber . "%' ";
			$first = true;
		}
		if ($name != "") {
			if ($first) {
				$sQ .= "AND ";
			}
			$sQ .= "CSM_SR_NAME LIKE '%" . $name . "%' ";
			$first = true;
		}
		if ($agentId != "") {
			if ($first) {
				$sQ .= "AND ";
			}
			$sQ .= "CSM_SR_AGENT_ID LIKE '%" . $agentId . "%' ";
			$first = true;
		}
		if ($status != "" && $status != -1) {
			if ($first) {
				$sQ .= "AND ";
			}
			$sQ .= "CSM_SR_STATUS = '" . $status . "' ";
			$first = true;
		}
		$sQ .= "ORDER BY CSM_SR_STATUS ASC";
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$terminal[$i]["name"] = $row["CSM_SR_NAME"];
					$terminal[$i]["bank"] = $row["CSM_SR_BANK"];
					$terminal[$i]["ppid"] = $row["CSM_SR_PPID"];
					$terminal[$i]["phoneNumber"] = $row["CSM_SR_PHONE_NUMBER"];
					$terminal[$i]["accountNumber"] = $row["CSM_SR_ACCOUNT_NUMBER"];
					$terminal[$i]["agentId"] = $row["CSM_SR_AGENT_ID"];
					$terminal[$i]["flag"] = $row["CSM_SR_FLAG"];
					$terminal[$i]["status"] = $row["CSM_SR_STATUS"];
					$terminal[$i]["initSetoran"] = $row["CSM_SR_INITIAL_SETORAN"];
					$terminal[$i]["initDeposit"] = $row["CSM_SR_INITIAL_DEPOSIT"];
					$i++;
				}
			}
		}

		return $terminal;
	}

	/* === e-Voucher === */
	public function GetTerminalMapping($ppid, &$map)
	{
		$ppid = mysqli_real_escape_string($this->DBLink, $ppid);

		$bOK = false;
		$sQ = "SELECT * FROM cscmod_voucher_sms_mapping WHERE CSM_SM_PPID = '$ppid'";
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$map[$i]['PHONE_NUMBER'] = $row['CSM_SM_PHONE_NUMBER'];
					$i++;
				}
			}
		}

		return $bOK;
	}

	public function isTerminalPin($phone, $pin)
	{
		$phone = mysqli_real_escape_string($this->DBLink, $phone);

		$bOK = false;


		$sQ = "SELECT * FROM cscmod_voucher_sms_mapping WHERE CSM_SM_PHONE_NUMBER = '$phone' AND CSM_SM_PIN_DIGEST = '" . md5($pin) . "'";
		// echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
			}
		}

		return $bOK;
	}

	public function setAddReg($phone, $pin, $ppid)
	{
		$phone = mysqli_real_escape_string($this->DBLink, $phone);
		$pin = mysqli_real_escape_string($this->DBLink, $pin);
		$ppid = mysqli_real_escape_string($this->DBLink, $ppid);

		$bOK = false;
		$sQ = "INSERT INTO cscmod_voucher_sms_mapping VALUES ('$phone','$ppid',1,'" . md5($pin) . "')";

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		} else {
			echo mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	public function setNewPin($phone, $pin)
	{
		$phone = mysqli_real_escape_string($this->DBLink, $phone);
		$pin = mysqli_real_escape_string($this->DBLink, $pin);

		$bOK = false;
		$sQ = "UPDATE cscmod_voucher_sms_mapping SET CSM_SM_PIN_DIGEST='" . md5($pin) . "' WHERE CSM_SM_PHONE_NUMBER='$phone'";

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		} else {
			echo mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	public function setConfirmDeposit($id, $number, $dt, $method, $bank, $account, $name, $ppid, $deposit)
	{
		$number = mysqli_real_escape_string($this->DBLink, $number);
		$bank = mysqli_real_escape_string($this->DBLink, $bank);
		$account = mysqli_real_escape_string($this->DBLink, $account);
		$name = mysqli_real_escape_string($this->DBLink, $name);
		$ppid = mysqli_real_escape_string($this->DBLink, $ppid);
		$deposit = mysqli_real_escape_string($this->DBLink, $deposit);

		$bOK = false;
		$sQ = "
			INSERT INTO csccore_confirmed_deposit (
				CSC_CP_ID, 
				CSC_CP_SENDER, 
				CSC_CP_DATE, 
				CSC_CP_METHOD, 
				CSC_CP_BANK, 
				CSC_CP_ACC_NUMBER, 
				CSC_CP_NAME, 
				CSC_CP_PPID, 
				CSC_CP_DEPOSIT, 
				CSC_CP_STATUS, 
				CSC_CP_SENT_DATE
				)
			VALUES (
				'$id',
				'$number',
				'$dt',
				'$method',
				'$bank',
				'$account',
				'$name',
				'$ppid',
				'$deposit',
				'0',
				now()
				)
				";

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		} else {
			echo mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	public function getConfirmDeposit($ppid, $limit = null, &$result)
	{
		$ppid = mysqli_real_escape_string($this->DBLink, $ppid);
		$bOK = false;

		$sQCond = " WHERE CSC_CP_PPID='$ppid' ";

		$sQOrder = " ORDER BY CSC_CP_SENT_DATE ASC ";

		$sQLimit = ($limit != null) ? " LIMIT $limit " : "";

		$sQ = "SELECT * FROM csccore_confirmed_deposit ";
		$sQ .= $sQCond;
		$sQ .= $sQOrder;
		$sQ .= $sQLimit;
		//echo $sQ;

		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . $this->sThisFile . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$result[$i] = $row;
					$i++;
				}
			}
		}

		return $bOK;
	}

	public function setKeluhan($id, $ppid, $msg, $msgType)
	{
		$msg = mysqli_real_escape_string($this->DBLink, $msg);
		$msgType = mysqli_real_escape_string($this->DBLink, $msgType);

		$bOK = false;
		$sQ = "
			INSERT INTO cpccore_payment_point_message (
				CPC_PPM_ID, 
				CPC_PPM_PPID, 
				CPC_PPM_MODULE, 
				CPC_PPM_MSG, 
				CPC_PPM_SENT, 
				CPC_PPM_MSGTYPE
				)
			VALUES (
				'$id',
				'$ppid',
				'e-Voucher',
				'$msg',
				now(),
				'$msgType'
				)
				";

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		} else {
			echo mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	public function getKeluhan($ppid, $msg, &$result)
	{
		$msg = mysqli_real_escape_string($this->DBLink, $msg);
		$ppid = mysqli_real_escape_string($this->DBLink, $ppid);

		$bOK = false;
		$sQ = "
			SELECT CPC_PPM_MODULE, CPC_PPM_MSG, CPC_PPM_SENT, CPC_PPM_MSGTYPE, CPC_PPM_STATUS, CPC_PPM_REASON 
			FROM cpccore_payment_point_message 
			WHERE CPC_PPM_PPID = '$ppid'
			  AND CPC_PPM_MSG LIKE '%$msg%'
			";

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$result[$i] = $row;
					$i++;
				}
			}
		} else {
			echo mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	/* === e-Admin === */
	public function InsertNewAgentMaster($name, $pin, &$id)
	{
		$name = mysqli_real_escape_string($this->DBLink, $name);
		$pin = mysqli_real_escape_string($this->DBLink, $pin);

		$bOK = false;

		$id = strtoupper(sprintf('%04x%04x%04x%04x', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)));

		$sQ = "INSERT INTO cscmod_voucher_sms_master_agent(CSM_MA_AGENT_ID,CSM_MA_NAME,CSM_MA_PIN_DIGEST) VALUES ('$id','$name','" . md5($pin) . "')";

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		} else {
			echo mysqli_errno($this->DBLink) . " : " . mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	public function InsertAgentPhone($id, $phone)
	{
		$phone = mysqli_real_escape_string($this->DBLink, $phone);

		$bOK = false;

		$sQ = "INSERT INTO cscmod_voucher_sms_agent_phone(CSM_AP_AGENT_ID,CSM_AP_PHONE_NUMBER) VALUES ('$id','$phone')";

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		} else {
			echo mysqli_errno($this->DBLink) . " : " . mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	public function EraseAgentMaster($id)
	{
		$bOK = false;

		$sQ = "DELETE FROM cscmod_voucher_sms_master_agent WHERE CSM_MA_AGENT_ID='$id'";

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		} else {
			echo mysqli_errno($this->DBLink) . " : " . mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	public function getAgentMasterAll(&$result)
	{
		$bOK = false;

		$sQ = "SELECT CSM_MA_AGENT_ID,CSM_MA_NAME FROM cscmod_voucher_sms_master_agent";

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$result[$i] = $row;
					$i++;
				}
			}
		} else {
			echo mysqli_errno($this->DBLink) . " : " . mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	public function getAgentPhone($id, &$result)
	{
		$bOK = false;

		$sQ = "SELECT CSM_AP_PHONE_NUMBER FROM cscmod_voucher_sms_agent_phone WHERE CSM_AP_AGENT_ID='$id'";

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$result[$i] = $row['CSM_AP_PHONE_NUMBER'];
					$i++;
				}
			}
		} else {
			echo mysqli_errno($this->DBLink) . " : " . mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	public function setAgentPIN($id, $oldpin, $newpin)
	{
		$bOK = false;

		$sQ = "SELECT CSM_MA_AGENT_ID FROM cscmod_voucher_sms_master_agent WHERE CSM_MA_AGENT_ID='$id' AND CSM_MA_PIN_DIGEST='" . md5($oldpin) . "'";

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$sQ = "UPDATE cscmod_voucher_sms_master_agent SET CSM_MA_PIN_DIGEST='" . md5($newpin) . "' WHERE CSM_MA_AGENT_ID='$id'";

				if ($res = mysqli_query($this->DBLink, $sQ)) {
					$bOK = true;
				} else {
					echo mysqli_errno($this->DBLink) . " : " . mysqli_error($this->DBLink);
				}
			}
		} else {
			echo mysqli_errno($this->DBLink) . " : " . mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	public function isAgentPIN($id, $pin)
	{
		$bOK = false;

		$sQ = "SELECT CSM_MA_AGENT_ID FROM cscmod_voucher_sms_master_agent WHERE CSM_MA_AGENT_ID='$id' AND CSM_MA_PIN_DIGEST='" . md5($pin) . "'";

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
			}
		} else {
			echo mysqli_errno($this->DBLink) . " : " . mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	/* === e-Agent === */
	public function InsertNewPPMaster($phone, $name, $pin, $bank, $accNum, $deposit, $agent, $ppid, $vsiAcc, $ppsid)
	{
		$phone = mysqli_real_escape_string($this->DBLink, $phone);
		$name = mysqli_real_escape_string($this->DBLink, $name);
		$pin = mysqli_real_escape_string($this->DBLink, $pin);
		$bank = mysqli_real_escape_string($this->DBLink, $bank);
		$accNum = mysqli_real_escape_string($this->DBLink, $accNum);
		$deposit = mysqli_real_escape_string($this->DBLink, $deposit);
		$ppid = mysqli_real_escape_string($this->DBLink, $ppid);

		$bOK = false;

		$sQ = "
			INSERT INTO cscmod_voucher_sms_reg 
			(CSM_SR_PHONE_NUMBER, CSM_SR_PPID, CSM_SR_ACCOUNT_NUMBER, CSM_SR_NAME, CSM_SR_BANK, CSM_SR_ACC_NUMBER, CSM_SR_STATUS, CSM_SR_INITIAL_SETORAN, CSM_SR_PIN_DIGEST, CSM_SR_AGENT_ID) 
			VALUES 
			('$phone','$ppid','$vsiAcc','$name','$bank','$accNum', 0, $deposit,'" . md5($pin) . "','$agent')";

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		} else {
			echo mysqli_errno($this->DBLink) . " : " . mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	public function getPPMaster($agentId, &$result)
	{
		$bOK = false;

		$sQ = "SELECT * FROM cscmod_voucher_sms_reg WHERE CSM_SR_AGENT_ID='$agentId'";

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$result[$i] = $row;
					$i++;
				}
			}
		} else {
			echo mysqli_errno($this->DBLink) . " : " . mysqli_error($this->DBLink);
		}

		return $bOK;
	}

	public function getMaxPPID()
	{
		$sQ = "SELECT CDC_LUP_NUMBER FROM cdccore_last_used_ppid";
		$max = false;

		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$row = mysqli_fetch_array($res);
				$max = $row['CDC_LUP_NUMBER'];
			}
		} else {
			echo mysqli_errno($this->DBLink) . " : " . mysqli_error($this->DBLink);
		}

		return $max;
	}
}
