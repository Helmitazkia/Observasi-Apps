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

    function getData()
	{
	
	    $dataOut = array();
		$trNya = "";
		$no = 1;

		
		$dataOutVessel     = $this->getVessel();
		$dataOutVesselType = $this->getVesselType();

	
		$userId   = $this->session->userdata('userId');
		$userType = strtolower($this->session->userdata('userType'));

		

		$txtSearch = $this->input->post('txtSearch', true);
		$startDate = $this->input->post('startDate', true);
		$endDate   = $this->input->post('endDate', true);
		$slcVessel = $this->input->post('slcVessel', true);

		$whereNya = " WHERE 1=1";

		
		$idVesselLogin    = $this->session->userdata('idVesselLogin');
		$vesselTypeLogin  = $this->session->userdata('vesselTypeLogin');
		// $namevessel = "select name from mst_vessel where id = '".$idVesselLogin."'";
		$idjabatan = $this->session->userdata('idJabatan');
		$id_osVesselLogin = $this->session->userdata('os_vessel');


		##$getnamevessel = $this->db->query("SELECT name FROM mst_vessel WHERE id = '".$idVesselLogin."'");

		// var_dump($namevessel);exit;
		
		// ubah string "1,5" menjadi "'1','5'"
		if ($idjabatan == "1"){
			$ids = "'" . str_replace(",", "','", $idVesselLogin) . "'";
		}else{
			 $ids = "'" . str_replace(",", "','", $id_osVesselLogin) . "'";
		}
		// $ids = "'" . str_replace(",", "','", $id_osVesselLogin) . "'";

		$sql = "SELECT name FROM mst_vessel WHERE id IN ($ids)";
		$get_nameVessel_os = $this->db->query($sql)->result();

		//var_dump($get_nameVessel_os); // Debugging line to check the output
		if ($userType != 'admin') {

			 // Jika OS punya multiple vessel
			if (!empty($get_nameVessel_os)) {

				$namaList = array();
				foreach ($get_nameVessel_os as $v) {
					$namaList[] = "'" . $v->name . "'";
				}

				// Buat string IN('A','B')
				$namaStr = implode(",", $namaList);

				// Filter berdasarkan vessel OS
				$whereNya .= " AND mf.vessel IN ($namaStr)";	
			}
			// else{
			// 		#DECK AND ENGINE JABATAN
			// 		$whereNya .= " WHERE 1=1";
			// 	}
				// }else if($idjabatan =="998" || $idjabatan =="997"){
				// 		$whereNya = " WHERE 1=1";
				// 		var_dump("test where");
				// }else{	
				// 	if ($getnamevessel->num_rows() > 0) {
				// 		$row = $getnamevessel->row();
				// 		$namevessel = $row->name;
				// 		$whereNya .= " AND mf.vessel = '".$namevessel."'";
				// 	} 
				// }
			


		}

		// var_dump($whereNya);exit; // Debugging line to check the output

		if (!empty($txtSearch)) {

			$whereNya .= " AND (
				lf.name_user LIKE '%".$txtSearch."%' 
				OR lf.category LIKE '%".$txtSearch."%' 
				OR lf.filename LIKE '%".$txtSearch."%' 
				OR lf.vessel LIKE '%".$txtSearch."%'
				OR lf.vesselType LIKE '%".$txtSearch."%'
			)";

			if (!empty($slcVessel) && $slcVessel != "all") {
				$whereNya .= " AND lf.vessel = '".$slcVessel."'";
			}
		}


		if (!empty($startDate) && !empty($endDate)) {

			$whereNya .= " AND DATE(lf.upload_time) BETWEEN '".$startDate."' AND '".$endDate."'";
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
				$filenewLink = "";

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
					$filenewLink .= "
						<a href='" . base_url($val->file) . "' target='_blank'
						style=\"
							color:#28a745;
							font-size:13px;
							font-weight:500;
							text-decoration:none;
						\"
						onmouseover=\"this.style.textDecoration='underline'; this.style.color='#1e7e34'\"
						onmouseout=\"this.style.textDecoration='none'; this.style.color='#28a745'\">
						View
						</a>
					";
				}

				$trNya .= "<tr style='transition:all 0.25s ease; background-color:#ffffff; border-bottom:1px solid #f1f5f9;' 
							onmouseover=\"this.style.backgroundColor='#f9fafb'; this.style.transform='scale(1.005)';\" 
							onmouseout=\"this.style.backgroundColor='#ffffff'; this.style.transform='scale(1)';\">";

				$trNya .= "<td style='font-size:13px; text-align:center; color:#1f2937; padding:10px 12px; font-weight:500;'>".$no."</td>";
				$trNya .= "<td style='font-size:13px; color:#111827; padding:10px 12px; font-weight:500;'>".$val->name_user."</td>";
				$trNya .= "<td style='padding:10px;color:#333;'>".$val->vessel."</td>";
				$trNya .= "<td style='font-size:13px; color:#4b5563; padding:10px 12px;'>".$val->departement."</td>";
				$trNya .= "<td style='font-size:13px; color:#2563eb; padding:10px 12px; font-weight:500;'>".$val->filename."</td>";
				// $trNya .= "<td style='font-size:13px; color:#4b5563; padding:10px 12px;'>".$val->vesselType."</td>";
				$trNya .= "<td style='font-size:13px; padding:10px 12px;'>".$filenewLink."</td>";
				
				$badgeColor = "#dbeafe"; $textColor = "#1d4ed8";
				if (stripos($val->category, 'Engine') !== false) { $badgeColor = "#dcfce7"; $textColor = "#166534"; }
				else if (stripos($val->category, 'Deck') !== false) { $badgeColor = "#fef9c3"; $textColor = "#92400e"; }
				else if (stripos($val->category, 'Other') !== false) { $badgeColor = "#f3f4f6"; $textColor = "#374151"; }
				$trNya .= "<td style='font-size:12px; padding:10px 12px;'>
								<span style=\"background:$badgeColor; color:$textColor; padding:4px 10px; border-radius:12px; font-weight:600; font-size:12px; display:inline-block; min-width:70px; text-align:center;\">
									".$val->category."
								</span>
						</td>";
				// $trNya .= "<td style='font-size:13px; padding:10px 12px;'>".$fileLinks."</td>";
			



				// $trNya .= "<td style='font-size:13px; color:#4b5563; padding:10px 12px;'>".$val->remarks."</td>";  
				// Gabungkan kedua remarks dengan pemisah <br>
				$allRemarks = '';

				if (!empty($val->remarks)) {
					$allRemarks .= "<div style='background-color:#e6fffa; color:#065f46; padding:5px; margin:2px 0; border-radius:3px;'>
						{$val->remarks}
					</div>";
				}
				if (!empty($val->remaks_revisi)) {
					$formattedDate = date('d M Y H:i', strtotime($val->date_revisi));

					$allRemarks .= "
						<div style='background-color:#fff5f5; color:#c53030; padding:5px; margin:2px 0; border-radius:3px;'>
							{$formattedDate} - {$val->remaks_revisi}
						</div>
					";
				}


				if (empty($allRemarks)) {
					$allRemarks = "-";
				}

				$trNya .= "<td style='font-size:13px; padding:10px 12px;'>{$allRemarks}</td>";
			
				// $trNya .= "<td style='text-align:center; font-size:12px; color:#6b7280; padding:10px 12px;'>".$val->download_time."</td>";
				$trNya .= "<td style='text-align:center; font-size:12px; color:#4b5563; padding:10px 12px;'>". date('d M Y H:i', strtotime($val->upload_time)) ."</td>";

				// $trNya .= "<td style='text-align:center; padding:10px 12px;'>".$btnAct."</td>";

				
				// $masterStatus = ($val->status_master == "Y")
				// 	? "<span style='color:green; font-weight:bold;'>" . date('d M Y H:i', strtotime($val->date_master)) . "</span>"
				// 	: "
				// 		<button onclick=\"updateStatus('update-status-master', '{$val->id_file}')\"
				// 			style='background:#00CC66; color:white; padding:5px 10px; border-radius:6px;
				// 			border:none; cursor:pointer; font-size:11px; font-weight:bold;'>
				// 			APPROVE
				// 		</button>
				// 	";

				// $trNya .= "
				// 	<td style='text-align:center; font-size:12px; padding:10px 8px;'>
				// 		{$masterStatus}
				// 	</td>
				// ";

				if ($val->status_master == "Y") {

					// Sudah approved → tampilkan tanggalnya
					$masterStatus = "<span style='color:green; font-weight:bold;'>" 
									. date('d M Y H:i', strtotime($val->date_master)) . 
									"</span>";

				} else {

					if ($idjabatan == "1" || $userType == "admin") {
						// Belum Y dan jabatan = 1 → tombol aktif
						$masterStatus = "
							<button onclick=\"updateStatus('update-status-master', '{$val->id_file}')\"
								style='background:#00CC66; color:white; padding:5px 10px; border-radius:6px;
								border:none; cursor:pointer; font-size:11px; font-weight:bold;'>
								APPROVE
							</button>
						";
					} else {
						// Belum Y dan jabatan ≠ 1 → tidak boleh approve → tampilkan span kosong
						$masterStatus = "<span style='color:#999; font-size:12px; font-style:italic;'></span>";
					}
				}

				$trNya .= "
					<td style='text-align:center; font-size:12px; padding:10px 8px;'>
						{$masterStatus}
					</td>
				";




				if ($val->status_os == "Y") {
					$osStatus = "<span style='color:green; font-weight:bold;'>" . date('d M Y H:i', strtotime($val->date_os)) . "</span>";
				} else {
					if ($val->status_master == "Y" && ($idjabatan == "999" || $userType == "admin")) {
						$osStatus = "
							<button onclick=\"updateStatus('update-status-os', '{$val->id_file}')\"
								style='background:#0080FF; color:white; padding:5px 10px; border-radius:6px;
								border:none; cursor:pointer; font-size:11px; font-weight:bold;'>
								REVIEW
							</button>
						";
					} else {
					
						$osStatus = "<span style='color:#999; font-size:12px; font-style:italic;'></span>";
					}
				}

				$trNya .= "
					<td style='text-align:center; font-size:12px; padding:10px 8px;'>
						{$osStatus}
					</td>
				";


				// ==========================
				// 3. DECK
				// ==========================
				// Jika departement ENGINE → tombol DECK tidak boleh tampil
				if ($val->departement == "ENGINE") {

					// Kosongkan (atau bisa kasih tanda - )
					$deckStatus = "<span style='color:#999; font-size:12px; font-style:italic;'>-</span>";

				} else {
				// Kalau sudah punya status ENGINE → tampilkan tanggal/status
					if ($val->status_deck == "Y") {
						$deckStatus = "<span style='color:green; font-weight:bold;'>" . date('d M Y H:i', strtotime($val->date_deck)) . "</span>";
					} else {
						// Cek status OS
						if ($val->status_os == 'Y' && ($idjabatan == "998" || $userType == "admin")) {
							// Jika OS sudah approved, tampilkan button REVIEW
							$deckStatus = "
								<button onclick=\"updateStatus('update-status-deck', '{$val->id_file}')\"
									style='background:#0080FF; color:white; padding:5px 10px; border-radius:6px;
									border:none; cursor:pointer; font-size:11px; font-weight:bold;'>
									REVIEW
								</button>
							";
						} else {
							// Jika OS belum approved, tampilkan span kosong
							$deckStatus = "<span style='color:#999; font-size:12px;'></span>";
						}
					}
				}

				$trNya .= "
					<td style='text-align:center; font-size:12px; padding:10px 8px;'>
						{$deckStatus}
					</td>
				";


				// // ==========================
				// // 4. ENGINE
				// // ==========================
				// // Jika departement DECK → tombol ENGINE tidak boleh tampil
				// 	if ($val->departement == "DECK") {
				// 		// Kosongkan (atau bisa diganti simbol "-")
				// 		$engineStatus = "<span style='color:#999; font-size:12px; font-style:italic;'>-</span>";

				// 	} else {

				// 		// Kalau sudah punya status ENGINE → tampilkan tanggal/status
				// 		$engineStatus = ($val->status_engine == "Y")
				// 		? "<span style='color:green; font-weight:bold;'>" . date('d M Y H:i', strtotime($val->date_engine)) . "</span>"
				// 		: "
				// 			<button " . ($val->status_os == 'N' ? "disabled" : "") . "
				// 				onclick=\"" . ($val->status_os= 'Y' ? "updateStatus('update-status-engine', '{$val->id_file}')" : "") . "\"
				// 				style='background:" . ($val->status_os == 'N' ? "#CCCCCC" : "#0080FF") . "; 
				// 					color:" . ($val->status_os == 'N' ? "#666666" : "white") . "; 
				// 					padding:5px 10px; border-radius:6px;
				// 					border:none; cursor:" . ($val->status_os == 'N' ? "not-allowed" : "pointer") . "; 
				// 					font-size:11px; font-weight:bold;'>
				// 				REVIEW
				// 			</button>
				// 		";
				// 	}

				// 	$trNya .= "
				// 		<td style='text-align:center; font-size:12px; padding:10px 8px;'>
				// 			{$engineStatus}
				// 		</td>
				// 	";

				// ==========================
				// 4. ENGINE
				// ==========================
				// Jika departement ENGINE → tombol DECK tidak boleh tampil
				if ($val->departement == "DECK") {

					// Kosongkan (atau bisa kasih tanda - )
					$engineStatus = "<span style='color:#999; font-size:12px; font-style:italic;'>-</span>";

				} else {
				// Kalau sudah punya status ENGINE → tampilkan tanggal/status
					if ($val->status_engine == "Y") {
						$engineStatus = "<span style='color:green; font-weight:bold;'>" . date('d M Y H:i', strtotime($val->date_engine)) . "</span>";
					} else {
						// Cek status OS
						if ($val->status_os == 'Y' && ($idjabatan == "997" || $userType == "admin")) {
							// Jika OS sudah approved, tampilkan button REVIEW
							$engineStatus = "
								<button onclick=\"updateStatus('update-status-engine', '{$val->id_file}')\"
									style='background:#0080FF; color:white; padding:5px 10px; border-radius:6px;
									border:none; cursor:pointer; font-size:11px; font-weight:bold;'>
									REVIEW
								</button>
							";
						} else {
							// Jika OS belum approved, tampilkan span kosong
							$engineStatus = "<span style='color:#999; font-size:12px;'></span>";
						}
					}
				}

				$trNya .= "
					<td style='text-align:center; font-size:12px; padding:10px 8px;'>
						{$engineStatus}
					</td>
				";

				// Jika departement ENGINE → tombol DECK tidak boleh tampil
				if ($val->status_data == "N") {

					// Kosongkan (atau bisa kasih tanda - )
					$deckStatus = "<span style='color:#FFA500; font-size:12px; font-style:italic;'>PROCESSED</span>";

				} else {

					$deckStatus = "<span style='color:green; font-size:12px; font-weight:bold;'>COMPLETE</span>";
				}

				$trNya .= "
					<td style='text-align:center; font-size:12px; padding:10px 8px;'>
						{$deckStatus}
					</td>
				";


				$trNya .= "</tr>";

				$no++;
			}
		}

		$dataOut['trNya'] = $trNya;
		$dataOut["vessel"] = $dataOutVessel;
		$dataOut['vesselType'] = $dataOutVesselType;

		if ($this->input->is_ajax_request()) {
			echo json_encode($dataOut);
			return;
		}

		$this->load->view('front/listFile', $dataOut);

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


	function getFileName()
	{
		$vessel     = strtolower($this->input->post('vessel', true));
		$vesselType = strtolower($this->input->post('vesselType', true));
		$category   = strtolower($this->input->post('category', true));

		$sql   = "SELECT DISTINCT id,filename FROM mstfile WHERE deletests = 0";
		$where = array();

		if ($vessel !== 'all') {
			$where[] = "LOWER(vessel) = " . $this->db->escape($vessel);
		}

		if ($vesselType !== 'all') {
			$where[] = "LOWER(vesselType) = " . $this->db->escape($vesselType);
		}

		if (!empty($category)) {
			$where[] = "LOWER(category) = " . $this->db->escape($category);
		}

		if (!empty($where)) {
			$sql .= " AND " . implode(" AND ", $where);
		}

		$sql .= " ORDER BY filename ASC";

		$rsl = $this->observasi->getDataQuery($sql);

		$out = array();
		foreach ($rsl as $row) {
			$out[] = array(
				"label" => $row->filename,
				"value" => $row->id
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
							class='#'>
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
		// $idFile   = $this->input->post("idFile", true); 
		$idFile   = $this->input->post("slcFileNameUpload", true);
		$fullName = $this->session->userdata('fullName');
		$userId   = $this->session->userdata('userId');
		$dateNow  = date("Y-m-d H:i:s");
		$departement = $this->input->post("slcDepartementUpload", true);
		$action = $this->input->post("action", true);
		// Cek jika ada flag revision
		$flagRevision = $this->input->post('flagrevision', true);
		$remaks_revisi = $this->input->post('remaks_revisi', true);
		$status_revisi = $this->input->post('status_revisi', true); // R = Revision

		// var_dump($flagRevision, $remaks_revisi, $status_revisi);exit;

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

		if ($action == "upload-file-update" &&  $cek && count($cek) > 0) {
			$this->db->where("id_file", $idFile);


			$this->db->update("listFile", array(
				"user_id"     => $userId,
				"name_user"   => $fullName,
				"upload_time" => $dateNow,
				"file"        => $rawFileName,
				"departement" => $departement 
			));

			print json_encode(array("status" => "success", "message" => "✅ Upload Success..!!"));
		}
		else if($action =="update-status-master" &&  $cek && count($cek) > 0) {
			$this->db->where("id_file", $idFile);
			$this->db->update("listFile", array(
				"userid_master"  => $userId,
				"date_master"    => $dateNow,
				"status_master"  => "Y",
				"status_revisi" => "N",
				"remaks_revisi" => "",
				"date_revisi" => "0000-00-00 00:00:00",
			));

			print json_encode(array("status" => "success", "message" => " Success Approved Master !!"));
		}
		// else if ($action =="update-status-os" &&  $cek && count($cek) > 0) {
		// 	$this->db->where("id_file", $idFile);
		// 	$this->db->update("listFile", array(
		// 		"userid_os"  => $userId,
		// 		"date_os"    => $dateNow,
		// 		"status_os"  => "Y"	
		// 	));

		// 	print json_encode(array("status" => "success", "message" => " Success Approved OS !!"));
		// }

		else if ($action == "update-status-os" && $cek && count($cek) > 0) {
			if ($flagRevision == "X") {
				// Jika revision requested
				$updateData = array(
					"remaks_revisi" => $remaks_revisi,
					"date_revisi"   => $dateNow,
					"status_revisi" => $status_revisi, // R = Revision
					"userid_revisi" => $userId,
					"status_master"     => "N",
					"status_data"   => "N"
				
				);
				
				$this->db->where("id_file", $idFile);
				$this->db->update("listFile", $updateData);
				
				print json_encode(array("status" => "success", "message" => "OS Revision requested!"));
				
			} else {
				// Jika approve normal
				$this->db->where("id_file", $idFile);
				$this->db->update("listFile", array(
					"userid_os"  => $userId,
					"date_os"    => $dateNow,
					"status_os"  => "Y",
					"status_revisi" => "N",
					"remaks_revisi" => "",
					"date_revisi" => "0000-00-00 00:00:00",
				));
				
				print json_encode(array("status" => "success", "message" => "Success Approved OS !!"));
			}
		}else if ($action =="update-status-deck" &&  $cek && count($cek) > 0) {
			
			if ($flagRevision == "X") {
				// Jika revision requested
				$updateData = array(
					"remaks_revisi" => $remaks_revisi,
					"date_revisi"   => $dateNow,
					"status_revisi" => $status_revisi, // R = Revision
					"userid_revisi" => $userId,
					"status_os"     => "N",
					"status_data"   => "N"
				);
				
				$this->db->where("id_file", $idFile);
				$this->db->update("listFile", $updateData);
				
				print json_encode(array("status" => "success", "message" => "DECK Revision requested!"));
				
			} else {
				// Jika approve normal
				$this->db->where("id_file", $idFile);
				$this->db->update("listFile", array(
					"userid_deck"  => $userId,
					"date_deck"    => $dateNow,
					"status_deck"  => "Y",
					"status_data" =>"Y"
				));
				
				print json_encode(array("status" => "success", "message" => " Success Review Deck !!"));
			}
		}else if ($action =="update-status-engine" &&  $cek && count($cek) > 0) {
			if ($flagRevision == "X") {
				// Jika revision requested
				$updateData = array(
					"remaks_revisi" => $remaks_revisi,
					"date_revisi"   => $dateNow,
					"status_revisi" => $status_revisi, // R = Revision
					"userid_revisi" => $userId,
					"status_os"     => "N",
					"status_data"   => "N"
				);
				
				$this->db->where("id_file", $idFile);
				$this->db->update("listFile", $updateData);
				
				print json_encode(array("status" => "success", "message" => "ENGINE Revision requested!"));
				
			} else {
				// Jika approve normal
				$this->db->where("id_file", $idFile);
				$this->db->update("listFile", array(
					"userid_engine"  => $userId,
					"date_engine"    => $dateNow,
					"status_engine"  => "Y",
					"status_data" =>"Y"
				));
				
				print json_encode(array("status" => "success", "message" => " Success Review Engine !!"));
			}
		}
		
		// else if ($action =="update-status-engine" &&  $cek && count($cek) > 0) {
		// 	$this->db->where("id_file", $idFile);
		// 	$this->db->update("listFile", array(
		// 		"userid_engine"  => $userId,
		// 		"date_engine"    => $dateNow,
		// 		"status_engine"  => "Y"
		// 	));

		// 	print json_encode(array("status" => "success", "message" => " Success Review Engine !!"));
		// }
		
		else {
			#insert new record
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
				"departement" => $departement,
				"upload_time"   => $dateNow
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