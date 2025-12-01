<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterForm extends CI_Controller{
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
        $whereNya = " WHERE a.deletests = '0' ";
        $dataOutVessel = $this->getVessel();
        $dataVesselType = $this->getVesselType();

        if ($searchNya == "search") {
            $txtSearch = $this->input->post('txtSearch', true);
            $slcVessel = $this->input->post('slcVessel', true);
            $slcVesselType = $this->input->post('slcVesselType', true); 

            if (!empty($txtSearch)) {
                $whereNya .= " AND (
                    a.vesselType LIKE '%" . $txtSearch . "%' 
                    OR a.category LIKE '%" . $txtSearch . "%' 
                    OR a.filename LIKE '%" . $txtSearch . "%'
                    OR a.remarks LIKE '%" . $txtSearch . "%'
                )";
            }

            if (!empty($slcVessel) && strtolower($slcVessel) != "all") {
                $whereNya .= " AND a.vessel = '" . $slcVessel . "'";
            }

            if (!empty($slcVesselType) && strtolower($slcVesselType) != "all") {
                $whereNya .= " AND a.vessel_type = '" . $slcVesselType . "'";
            }

        }

        $sql = "
            SELECT a.*, 
                CASE 
                    WHEN LOWER(a.vessel) = 'all' THEN 'All'
                    WHEN b.name IS NOT NULL THEN b.name
                    ELSE '-'
                END AS vessel_name
            FROM mstfile a
            LEFT JOIN mst_vessel b ON a.vessel = b.name
            $whereNya
            ORDER BY vessel_name ASC
        ";
        
        $rsl = $this->observasi->getDataQuery($sql);

        if (count($rsl) > 0) {
            foreach ($rsl as $val) {
                $btnAct = "
                    <button 
                        title='Edit'
                        onclick=\"editFile('".$val->id."');\" 
                        style=\"
                            background: linear-gradient(90deg, #ff6b6b, #ff4757);
                            border: none;
                            color: #fff;
                            padding: 6px 12px;
                            border-radius: 6px;
                            font-size: 12px;
                            font-weight: 600;
                            cursor: pointer;
                            transition: all 0.2s ease-in-out;
                            box-shadow: 0 2px 6px rgba(255, 71, 87, 0.2);
                        \" 
                        onmouseover=\"this.style.background='linear-gradient(90deg,#ff7b7b,#ff5c5c)';this.style.boxShadow='0 3px 8px rgba(255,71,87,0.3)';this.style.transform='translateY(-1px)';\" 
                        onmouseout=\"this.style.background='linear-gradient(90deg,#ff6b6b,#ff4757)';this.style.boxShadow='0 2px 6px rgba(255,71,87,0.2)';this.style.transform='translateY(0)';\"
                    >
                        ✏️ Edit
                    </button>
                ";

                $fileLink = !empty($val->uploadFile)
                    ? "<a href='".base_url($val->uploadFile)."' 
                        target='_blank' 
                        style='
                            color:#007bff;
                            font-weight:600;
                            text-decoration:none;
                            transition:color 0.2s;
                        ' 
                        onmouseover=\"this.style.color='#0056b3';\" 
                        onmouseout=\"this.style.color='#007bff';\">
                        🔗 View File
                    </a>"
                    : "<span style='color:#aaa;font-style:italic;'>No File</span>";

                $trNya .= "
                <tr 
                    style=\"
                        background-color:#fff;
                        border-bottom:1px solid #eee;
                        transition:all 0.25s ease;
                        font-size:13px;
                    \"
                    onmouseover=\"this.style.backgroundColor='#f9f9f9';\" 
                    onmouseout=\"this.style.backgroundColor='#fff';\"
                >
                    <td style='padding:10px;text-align:center;color:#444;font-weight:600;'>".$no."</td>
                    <td style='padding:10px;color:#333;'>".
                        (!empty($val->vessel_name) ? $val->vessel_name : 
                        (strtolower($val->vessel) == "all" ? "All" : "-")).
                    "</td>

                    <td style='padding:10px;color:#333;'>
                        ".(
                            strtolower($val->vesselType) == 'all'
                                ? 'All'
                                : ucfirst($val->vesselType)
                        )."
                    </td>

                    <td style='padding:10px;color:#333;font-weight:500;'>".$val->category."</td>
                    <td style='padding:10px;color:#333;'>".$val->filename."</td>
                    <td style='padding:10px;'>".$fileLink."</td>
                    <td style='padding:10px;color:#666;'>".$val->remarks."</td>
                    <td style='padding:10px;text-align:center;'>".$btnAct."</td>
                </tr>
                ";
                $no++;
            }
        }

        $dataOut['trNya'] = $trNya;
        $dataOut["vessel"] = $dataOutVessel;
        $dataOut["vesselType"] = $dataVesselType;
        if ($searchNya == "") {
            $this->load->view('front/masterForm', $dataOut);
        } else {
            print json_encode($dataOut);
        }
    }
    
   function saveFile()
    {
        $fullName = $this->session->userdata('fullName');
        $dateNow  = date("Y-m-d H:i:s");

        $idEdit         = $this->input->post("idEdit"); 
        $slcVesselType  = $this->input->post("slcVesselType");
        $categories     = $this->input->post("txtCategory");
        $filenames      = $this->input->post("fileName");
        $remarks        = $this->input->post("txtRemark");
        $vessels        = $this->input->post("slcVessel");

        if (!is_array($slcVesselType) || count($slcVesselType) == 0) {
            print json_encode(array("status" => "error", "message" => "No data to save."));
            return;
        }

        $totalData = count($slcVesselType);
        $success = 0;
        $failed = 0;

        for ($i = 0; $i < $totalData; $i++) {
            $vesselType = isset($slcVesselType[$i]) ? trim($slcVesselType[$i]) : "";
            $category   = isset($categories[$i]) ? trim($categories[$i]) : "";
            $filename   = isset($filenames[$i]) ? trim($filenames[$i]) : "";
            $remark     = isset($remarks[$i]) ? trim($remarks[$i]) : "";
            $vesselName = isset($vessels[$i]) ? trim($vessels[$i]) : "";

            if ($vesselName == "") $vesselName = "all";
            if ($vesselType == "") $vesselType = "all";

            $oldFilePath = "";
            if (!empty($idEdit)) {
                $qOld = $this->observasi->getDataQuery(
                    "SELECT uploadFile FROM mstfile WHERE id = " . $this->db->escape($idEdit)
                );
                if ($qOld && count($qOld) > 0) {
                    $oldFilePath = $qOld[0]->uploadFile;
                }
            }

            $uploadFile = $oldFilePath;

            if (!empty($_FILES["fileUpload"]["name"][$i])) {
                $targetDir = FCPATH . "uploads/masterfile/";
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

                $fileTmp = $_FILES["fileUpload"]["tmp_name"][$i];
                $ext     = pathinfo($_FILES["fileUpload"]["name"][$i], PATHINFO_EXTENSION);
                $tempId  = !empty($idEdit) ? $idEdit : (time() . rand(1000, 9999));
                $fileName = "MasterFile_" . $tempId . "." . $ext;
                $targetFile = $targetDir . $fileName;

                if (move_uploaded_file($fileTmp, $targetFile)) {
                    $uploadFile = "uploads/masterfile/" . $fileName;

                    if (!empty($oldFilePath) && file_exists(FCPATH . $oldFilePath)) {
                        unlink(FCPATH . $oldFilePath);
                    }
                } else {
                    $failed++;
                    continue;
                }
            }

            $dataSave = array(
                "vessel"      => $vesselName,        
                "vesselType"  => $vesselType,
                "category"    => $category,
                "filename"    => $filename,
                "uploadFile"  => $uploadFile,
                "remarks"     => $remark,
                "deletests"   => 0
            );

            if (empty($idEdit)) {
                $dataSave["addUserDate"] = $fullName . "#" . $dateNow;
                $this->db->insert("mstfile", $dataSave);
            } else {
                $dataSave["updUserDate"] = $fullName . "#" . $dateNow;
                $this->db->where("id", $idEdit);
                $this->db->update("mstfile", $dataSave);
            }

            $success++;
        }
        
        print "Save Success..!!";
    }



    function getCategories()
    {
        $search     = $this->input->get('term', true);
        $slcVesselType = $this->input->get('vesselType', true);
        $vessel     = $this->input->get('vessel', true);

        $sql = "SELECT DISTINCT category 
                FROM mstfile 
                WHERE deletests = 0 
                AND vesselType IS NOT NULL AND  vessel IS NOT NULL ";

        if (!empty($slcVesselType)) {
            $sql .= " AND vesselType = " . $this->db->escape($slcVesselType);
        }

        if (!empty($search)) {
            $sql .= " AND category LIKE '%" . $this->db->escape_like_str($search) . "%'";
        }

        if (!empty($vessel)) {
            $sql .= " AND vessel = " . $this->db->escape($vessel);
            
        }

        $sql .= " ORDER BY category ASC LIMIT 10";

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


    function getVessel($selectedName = "")
    {
        $dataOutVessel = "";
        $dataVessel = $this->observasi->getDataAll("mst_vessel");

        $dataOutVessel .= '<option value="">-- Select vessel --</option>';
        $dataOutVessel .= '<option value="all">All</option>';

        foreach ($dataVessel as $value) {
            $selected = ($selectedName == $value->name) ? "selected" : "";
            $dataOutVessel .= '<option value="' . htmlspecialchars($value->name) . '" ' . $selected . '>' . htmlspecialchars($value->name) . '</option>';
        }

        return $dataOutVessel;
    }


    function getFileById($id)
    {
        $sql = "SELECT *
                FROM mstfile 
                WHERE id = '".$id."' ";

        $rsl = $this->observasi->getDataQuery($sql, array($id));

        echo json_encode($rsl);
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