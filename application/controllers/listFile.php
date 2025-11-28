<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ListFile extends CI_Controller{
    function __construct()
	{
		parent::__construct();		
		$this->load->model('observasi');
		$this->load->helper(array('form', 'url'));
	}

    function index()
    {
        $this->getData();
    }

    function getData($searchNya = "")
	{
		$dataOut = array();
		$trNya = "";
		$no = 1;
		$dataOutVessel = $this->getVessel();
		$dataOutVesselType = $this->getVesselType();

		$userId   = $this->session->userdata('userId');
		$userType = strtolower($this->session->userdata('userType')); 

		$whereNya = " WHERE 1=1";

		if ($userType != 'admin') {
			$idVesselLogin = $this->session->userdata('idVesselLogin');
			$vesselTypeLogin = $this->session->userdata('vesselTypeLogin');

			$whereNya .= " AND (
				lf.user_id = '".$userId."'
				OR mf.vessel = 'all'
				OR mf.vesselType = 'all'
				OR mf.vessel = '".$idVesselLogin."'
				OR mf.vesselType = '".$vesselTypeLogin."'
			)";
		}

		if ($searchNya == "search") {
			$txtSearch = $this->input->post('txtSearch', true);
			$slcVessel = $this->input->post('slcVessel', true);

			if (!empty($txtSearch)) {
				$whereNya .= " AND (lf.department LIKE '%".$txtSearch."%' 
								OR lf.category LIKE '%".$txtSearch."%' 
								OR lf.file LIKE '%".$txtSearch."%' 
								OR lf.remarks LIKE '%".$txtSearch."%')";
			}

			if (!empty($slcVessel) && $slcVessel != "all") {
				$whereNya .= " AND lf.vessel = '".$slcVessel."'";
			}
		}

		$sql = "
			SELECT 
				lf.*, 
				mf.uploadFile AS raw_file,
				mv.name AS vessel_name
			FROM listFile lf
			LEFT JOIN mstfile mf ON lf.id_file = mf.id
			LEFT JOIN mst_vessel mv ON lf.vessel = mv.id
			$whereNya
			ORDER BY mv.name ASC
		";

		$rsl = $this->observasi->getDataQuery($sql);

		if (count($rsl) > 0) { 
			foreach ($rsl as $val) {
				$btnAct = "<button class='btn btn-success btn-xs btn-block' 
							title='Upload File' 
							onclick=\"openUploadModal('".$val->id_file."');\">Upload</button>";

				$fileLinks = "";

				if (!empty($val->raw_file)) {
					$filePath = str_replace("uploads/masterFileUpdate/", "uploads/masterfile/", $val->raw_file);
					$fileLinks .= "
						<a href='" . base_url($filePath) . "' target='_blank'
						style=\"
							color:#007bff;
							font-size:13px;
							font-weight:500;
							margin-right:8px;
							text-decoration:none;
						\"
						onmouseover=\"this.style.textDecoration='underline'; this.style.color='#0056b3'\"
						onmouseout=\"this.style.textDecoration='none'; this.style.color='#007bff'\">
						View Master File
						</a>
					";
				} else {
					$fileLinks .= "
						<span style=\"
							color:#999;
							font-style:italic;
							font-size:13px;
							margin-right:8px;
						\">
							No Master File
						</span>
					";
				}

				if (!empty($val->upload_time) && !empty($val->file)) {
					$fileLinks .= "
						<a href='" . base_url($val->file) . "' target='_blank'
						style=\"
							color:#28a745;
							font-size:13px;
							font-weight:500;
							text-decoration:none;
						\"
						onmouseover=\"this.style.textDecoration='underline'; this.style.color='#1e7e34'\"
						onmouseout=\"this.style.textDecoration='none'; this.style.color='#28a745'\">
						View Upload File
						</a>
					";
				}

				$trNya .= "<tr style='transition:all 0.25s ease; background-color:#ffffff; border-bottom:1px solid #f1f5f9;' 
							onmouseover=\"this.style.backgroundColor='#f9fafb'; this.style.transform='scale(1.005)';\" 
							onmouseout=\"this.style.backgroundColor='#ffffff'; this.style.transform='scale(1)';\">";

				$trNya .= "<td style='font-size:13px; text-align:center; color:#1f2937; padding:10px 12px; font-weight:500;'>".$no."</td>";
				$trNya .= "<td style='font-size:13px; color:#111827; padding:10px 12px; font-weight:500;'>".$val->name_user."</td>";
				$trNya .= "<td style='padding:10px;color:#333;'>".$val->vessel."</td>";
				$trNya .= "<td style='font-size:13px; color:#2563eb; padding:10px 12px; font-weight:500;'>".$val->filename."</td>";
				$trNya .= "<td style='font-size:13px; color:#4b5563; padding:10px 12px;'>".$val->vesselType."</td>";
				$trNya .= "<td style='font-size:13px; padding:10px 12px;'>".$fileLinks."</td>";

				$badgeColor = "#dbeafe"; $textColor = "#1d4ed8";
				if (stripos($val->category, 'Engine') !== false) { $badgeColor = "#dcfce7"; $textColor = "#166534"; }
				else if (stripos($val->category, 'Deck') !== false) { $badgeColor = "#fef9c3"; $textColor = "#92400e"; }
				else if (stripos($val->category, 'Other') !== false) { $badgeColor = "#f3f4f6"; $textColor = "#374151"; }

				$trNya .= "<td style='font-size:12px; padding:10px 12px;'>
								<span style=\"background:$badgeColor; color:$textColor; padding:4px 10px; border-radius:12px; font-weight:600; font-size:12px; display:inline-block; min-width:70px; text-align:center;\">
									".$val->category."
								</span>
						</td>";

				$trNya .= "<td style='font-size:13px; color:#4b5563; padding:10px 12px;'>".$val->remarks."</td>";  
				$trNya .= "<td style='text-align:center; font-size:12px; color:#6b7280; padding:10px 12px;'>".$val->download_time."</td>";
				$trNya .= "<td style='text-align:center; font-size:12px; color:#6b7280; padding:10px 12px;'>".$val->upload_time."</td>";

				$trNya .= "<td style='text-align:center; padding:10px 12px;'>".$btnAct."</td>";

				$trNya .= "</tr>";

				$no++;
			}
		}

		$dataOut['trNya'] = $trNya;
		$dataOut["vessel"] = $dataOutVessel;
		$dataOut['vesselType'] = $dataOutVesselType;
		if ($searchNya == "") {
			$this->load->view('front/listFile', $dataOut);
		} else {
			print json_encode($dataOut);
		}
	}

    function getVessel($id = "")
	{
		$dataOutVessel = "";
		$dataVessel = $this->observasi->getDataAll("mst_vessel");

		$dataOutVessel .= "<option value=\"\">-- Select vessel --</option>";
		$dataOutVessel .= "<option value=\"All\">All</option>";

		foreach ($dataVessel as $key => $value) 
		{
			$selected = ($id == $value->id) ? "selected" : "";
			$dataOutVessel .= "<option value=\"".$value->name."\" $selected>".$value->name."</option>";
		}

		return $dataOutVessel;
	}

    function getCategories()
	{
		$slcVesselType = strtolower($this->input->get('vesselType', true));
		$vesselInput   = strtolower($this->input->get('vessel', true));

		$sql = "SELECT DISTINCT category FROM mstfile WHERE deletests = 0";
		$where = array();

		if ($vesselInput == 'all' && $slcVesselType == 'all') {
			$where[] = "(vessel = 'all' AND vesselType = 'all')";
		} 
		elseif ($vesselInput != 'all' && $slcVesselType == 'all') {
			$where[] = "(vessel = " . $this->db->escape($vesselInput) . " OR vessel = 'all')";
			$where[] = "(vesselType = 'all')";
		} 
		elseif ($vesselInput == 'all' && $slcVesselType != 'all') {
			$where[] = "(vessel = 'all')";
			$where[] = "(vesselType = " . $this->db->escape($slcVesselType) . " OR vesselType = 'all')";
		} 
		else {
			
			$where[] = "(vessel = " . $this->db->escape($vesselInput) . ")";
			$where[] = "(vesselType = " . $this->db->escape($slcVesselType) . ")";
		}

		if (!empty($where)) {
			$sql .= " AND " . implode(" AND ", $where);
		}

		$sql .= " ORDER BY category ASC";
		$rsl = $this->observasi->getDataQuery($sql);

		$out = array();
		foreach ($rsl as $row) {
			$out[] = array(
				"label" => $row->category,
				"value" => $row->category
			);
		}

		echo json_encode($out);
	}


	function getFileData()
	{
		$vesselInput   = $this->input->post('vessel', true);
		$slcVesselType = strtolower($this->input->post('vesselType', true));
		$category      = $this->input->post('category', true);

		$vessel = strtolower($vesselInput);
		$vesselId = null;

		if (!empty($vesselInput) && strtolower($vesselInput) != 'all' && !is_numeric($vesselInput)) {
			$qVessel = $this->db->query("SELECT id FROM mst_vessel WHERE name = " . $this->db->escape($vesselInput) . " LIMIT 1")->row();
			if ($qVessel) {
				$vesselId = $qVessel->id;
			}
		} else {
			$vesselId = $vesselInput;
		}

		$sql = "
			SELECT 
				a.id,
				a.vessel,
				b.name AS vessel_name,
				a.vesselType,
				a.category,
				a.filename,
				a.uploadFile,
				a.remarks
			FROM mstfile a
			LEFT JOIN mst_vessel b ON LOWER(a.vessel) = LOWER(b.name)
			WHERE a.deletests = 0
		";


		$where = array();

		if ($vessel == 'all' && $slcVesselType == 'all') {
			$where[] = "(a.vessel = 'all' AND a.vesselType = 'all')";
		} elseif ($vessel != 'all' && $slcVesselType == 'all') {
			$where[] = "(a.vessel = " . $this->db->escape($vessel) . " OR a.vessel = 'all')";
			$where[] = "(a.vesselType = 'all')";
		} elseif ($vessel == 'all' && $slcVesselType != 'all') {
			$where[] = "(a.vessel = 'all')";
			$where[] = "(a.vesselType = " . $this->db->escape($slcVesselType) . " OR a.vesselType = 'all')";
		} else {
			$where[] = "(a.vessel = " . $this->db->escape($vessel) . " OR a.vessel = 'all')";
			$where[] = "(a.vesselType = " . $this->db->escape($slcVesselType) . " OR a.vesselType = 'all')";
		}


		if (!empty($category)) {
			$where[] = "a.category LIKE '%" . $this->db->escape_like_str($category) . "%'";
		}

		if (!empty($where)) {
			$sql .= " AND " . implode(" AND ", $where);
		}

		$sql .= " ORDER BY a.filename ASC";
		$rsl = $this->observasi->getDataQuery($sql);

		if (empty($rsl)) {
			echo "<tr><td colspan='7' style='text-align:center;color:red;font-weight:bold;'>No data found</td></tr>";
			return;
		}

		$no = 1;
		foreach ($rsl as $row) {
			$filePath   = base_url($row->uploadFile);
			$vesselName = ($row->vessel == 'all') ? 'All' : ($row->vessel_name ?: $row->vessel);
			$vesselType = ($row->vesselType == 'all') ? 'All' : ($row->vesselType ?: '-');
			$category   = $row->category ?: '-';
			$filename   = $row->filename ?: '-';
			$remarks    = $row->remarks ?: '-';

			echo "
				<tr style='color:black; font-size:13px; text-align:left;'>
					<td style='text-align:center; padding:6px 8px; border-bottom:1px solid #ccc;'>{$no}</td>
					<td style='padding:6px 8px; border-bottom:1px solid #ccc;'>{$vesselName}</td>
					<td style='padding:6px 8px; border-bottom:1px solid #ccc;'>{$vesselType}</td>
					<td style='padding:6px 8px; border-bottom:1px solid #ccc;'>{$category}</td>
					<td style='padding:6px 8px; border-bottom:1px solid #ccc;'>{$filename}</td>
					<td style='text-align:center; padding:6px 8px; border-bottom:1px solid #ccc;'>
						<a href='{$filePath}' 
							download
							data-id='{$row->id}'
							style='display:inline-block; background:#007bff; color:white; font-size:12px; padding:4px 10px; border-radius:4px; text-decoration:none;' 
							class='btnSaveFile'>
							<i class='fa fa-download'></i> Download
						</a>

					</td>
					<td style='padding:6px 8px; border-bottom:1px solid #ccc;'>{$remarks}</td>
				</tr>
			";
			$no++;
		}
	}

	function saveToListFile()
	{
		$idFile   = $this->input->post("idFile", true); 
		$fullName = $this->session->userdata('fullName');
		$userId   = $this->session->userdata('userId');
		$dateNow  = date("Y-m-d H:i:s");

		if (!$idFile) {
			echo json_encode(array("status" => "error", "message" => "idFile kosong"));
			return;
		}

		$query = $this->observasi->getDataQuery("SELECT * FROM mstfile WHERE id = " . $this->db->escape($idFile));
		if (!$query || count($query) == 0) {
			echo json_encode(array("status" => "error", "message" => "data mstfile tidak ditemukan"));
			return;
		}

		$row = $query[0];
		$rawFileName = $row->uploadFile; 

		if (!empty($_FILES['fileUpload']['name'])) {
			$targetDir = FCPATH . "uploads/masterFileUpdate/";
			if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

			$fileTmp  = $_FILES["fileUpload"]["tmp_name"];
			$ext = pathinfo($_FILES["fileUpload"]["name"], PATHINFO_EXTENSION);
			$fileName = "MasterFileUpdate_" . $idFile ."_".time().".".$ext;

			$targetFile = $targetDir . $fileName;

			if (move_uploaded_file($fileTmp, $targetFile)) {
				$rawFileName = "uploads/masterFileUpdate/" . $fileName;

				$this->db->where("id", $idFile);
				$this->db->update("mstfile", array(
					"updUserDate" => $fullName . "#" . $dateNow
				));
			} else {
				echo json_encode(array("status" => "error", "message" => "Gagal upload file baru"));
				return;
			}
		}

		if (empty($_FILES['fileUpload']['name'])) {
			$targetDir = FCPATH . "uploads/masterListFile/";
			if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

			$ext = pathinfo($row->uploadFile, PATHINFO_EXTENSION);
			$fileName = "MasterListFile_" . $idFile . "." . $ext;
			$dummyPath = $targetDir . $fileName;

			if (!file_exists($dummyPath)) {
				file_put_contents($dummyPath, ""); 
			}

			$rawFileName = "uploads/masterListFile/" . $fileName;
		}

		$cek = $this->observasi->getDataQuery("SELECT * FROM listFile WHERE id_file = " . $this->db->escape($idFile) . " LIMIT 1");

		if ($cek && count($cek) > 0) {
			$this->db->where("id_file", $idFile);
			$this->db->update("listFile", array(
				"upload_time" => $dateNow,
				"name_user"   => $fullName,
				"file"        => $rawFileName
			));

			print json_encode(array("status" => "success", "message" => "✅ Upload Success..!!"));
		} else {
			$dataIns = array(
				"id_file"       => $idFile,
				"name_user"     => $fullName,
				"user_id"       => $userId,
				"vessel"        => $row->vessel,
				"vesselType"    => $row->vesselType,
				"category"      => $row->category,
				"filename"      => $row->filename,
				"file"          => $rawFileName,
				"remarks"       => $row->remarks,
				"download_time" => $dateNow,
				"upload_time"   => ""
			);

			$this->db->insert("listFile", $dataIns);

			print json_encode(array("status" => "success", "message" => "✅ Download Success..!!"));
		}

		
	}

	function getVesselType($return = "")
    {
        $opt = "<option value=''>Select Vessel Type</option>"; 
        $opt .= "<option value='all'>All</option>"; 
        $whereNya = "Deletests = '0' 
                    AND DefType IN ('Bulk Carrier', 'OIL TANKER', 'CHEMICAL TANKER', 'FLOATING CRANE', 'TUG BOAT')";

        $sql = "
            SELECT *
            FROM tbltype
            WHERE $whereNya
            ORDER BY NmType ASC
        ";

        $rsl = $this->observasi->getDataQuery($sql);

        foreach ($rsl as $val) {
            $opt .= "<option value=\"" . htmlspecialchars($val->DefType, ENT_QUOTES, 'UTF-8') . "\">" 
                    . htmlspecialchars($val->DefType, ENT_QUOTES, 'UTF-8') . "</option>";
        }

        if ($return == "") {
            return $opt;
        } else {
            print json_encode($opt);
        }
    }



}