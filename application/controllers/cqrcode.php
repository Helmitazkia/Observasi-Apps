<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Cqrcode extends CI_Controller {

	function __construct()
	{
		parent::__construct();		
		$this->load->model('myapp');
		$this->load->helper(array('form', 'url'));
	}
	function index()
	{
		$this->getData();
	}
	function getData($searchNya = "")
	{
		$dataOut = array();
		$userId = $this->session->userdata('userId');
		$trNya = "";
		$no = 1;
		$whereNya = " WHERE st_delete = '0' ";

		if($searchNya != "")
		{
			if($_POST['txtSearch'] != "")
			{			
				$whereNya .= " AND fullname LIKE '%".$_POST['txtSearch']."%' ";
			}
		}

		$data = $this->myapp->getDataQueryDB6("SELECT * FROM qrcode ".$whereNya." ORDER BY fullname ASC");
		
		foreach ($data as $key => $val) 
		{
			$phoneNya = "";

			if($val->hp != "" AND $val->hp != "0")
			{
				$phoneNya .= "HP : &nbsp&nbsp".$val->hp;
			}
			if($val->phone_home != "" AND $val->phone_home != "0")
			{
				$phoneNya .= "<br>Home : &nbsp&nbsp".$val->phone_home;
			}
			if($val->phone_office != "" AND $val->phone_office != "0")
			{
				$phoneNya .= "<br>Office : &nbsp&nbsp".$val->phone_office;
			}

			$btnQrCode = "<button type=\"button\" id=\"btnQrCode\" class=\"btn btn-primary btn-xs btn-block\" onclick=\"viewQrCode('".$val->id."');\" title=\"View QR Code\">
							<i class=\"fa fa-qrcode\"></i> </button>";

			$trNya .= "<tr>
							<td align=\"center\">".$no.$btnQrCode."</td>
							<td align=\"center\">".$val->fullname."</td>
							<td>".$phoneNya."</td>
							<td>".$val->email."</td>
							<td>".$val->address."</td>
							<td align=\"center\">
								<button type=\"button\" id=\"btnEdit\" class=\"btn btn-success btn-xs btn-block\" onclick=\"editData('".$val->id."');\" >Edit</button>
								<button type=\"button\" id=\"btnDel\" class=\"btn btn-danger btn-xs btn-block\" onclick=\"delData('".$val->id."');\" >Delete</button>
							</td>
						</tr>";
			$no ++;
		}

		$dataOut["trNya"] = $trNya;
		
		if($searchNya == "")
		{
			$this->load->view('myApps/viewQrcode',$dataOut);
		}else{
			print json_encode($dataOut);
		}
	}
	function saveData()
	{
		$data = $_POST;
		$dateNow = date("Y-m-d");
		$stData = "";

		$usrInit = $this->session->userdata('userInitial');
		$usrAddLogin = $usrInit."#".date("H:i")."#".date("d/m/Y");

		$idEdit = $data['idEdit'];

		$dataIns['fullname'] = $data['fullName'];
		$dataIns['hp'] = $data['hp'];
		$dataIns['phone_home'] = $data['phoneHome'];
		$dataIns['phone_office'] = $data['phoneOffice'];
		$dataIns['address'] = $data['address'];
		$dataIns['office'] = $data['office'];
		$dataIns['position'] = $data['Position'];
		$dataIns['email'] = $data['email'];
		$dataIns['city'] = $data['city'];
		$dataIns['kode_pos'] = $data['kodePos'];
		$dataIns['country'] = $data['country'];

		try {
			if ($idEdit == "")
			{
				$dataIns['add_userId'] = $usrAddLogin;

				$this->myapp->insDataDb6($dataIns,"qrcode");
				
			}else{
				$dataIns['edit_userId'] = $usrAddLogin;

				$whereNya = "id = '".$idEdit."'";
				$this->myapp->updateDataDb6("qrcode",$dataIns,$whereNya);
			}
			$stData = "Success..!!";
		} catch (Exception $ex) {
			$stData = "Failed =>".$ex;
		}
		print json_encode($stData);
	}
	function createQrCode($type = '')
	{
		$config = array();
		$this->load->library('ciqrcode');
		$fileGen = "";

		if($type == "json")
		{
			$id = $_POST['id'];
		}

		$data = $this->myapp->getDataQueryDB6("SELECT * FROM qrcode WHERE id = '".$id."' ORDER BY fullname ASC");
		
		if(count($data) > 0)
		{
			$fullName = $data[0]->fullname;
		    $phone_celular = $data[0]->hp;
		    $phone_work = $data[0]->phone_office;
		    $phone_home = $data[0]->phone_home;
		    $organization = $data[0]->office;
		    $position = $data[0]->position;
		    $email = $data[0]->email;
		    $sortName = $fullName;
		    $name = $fullName;

		    $label = $data[0]->office;
		    $pobox = "";
		    $ext = "";
		    $street = $data[0]->address;
		    $town = $data[0]->city;
		    $post_code = $data[0]->kode_pos;
		    $country = $data[0]->country;

		    $isi_teks  = 'BEGIN:VCARD'."\n";
		    $isi_teks .= 'VERSION:4.0'."\n";
		    $isi_teks .= 'N:'.$sortName."\n";
		    $isi_teks .= 'FN:'.$name."\n";
		    $isi_teks .= 'ORG:'.$organization."\n";
		    $isi_teks .= 'TITLE:'.$position."\n";
		    $isi_teks .= 'TEL;TYPE=work,voice;VALUE=uri:tel:'.$phone_work."\n";
		    $isi_teks .= 'TEL;TYPE=home,voice;VALUE=uri:tel:'.$phone_home."\n";
		    $isi_teks .= 'TEL;TYPE=cell,voice;VALUE=uri:tel:'.$phone_celular."\n";

		    $isi_teks .= 'ADR;TYPE=HOME;'.'LABEL="'.$label.'":'
	        			.$pobox.';'
	        			.$ext.';'
	        			.$street.';'
	        			.$town.';'
	        			.$post_code.';'
	        			.$country."\n";
	    	$isi_teks .= 'EMAIL:'.$email."\n";
	    	$isi_teks .= 'END:VCARD';

	    	$config['cacheable']	= true;
			$config['cachedir']		= './assets/imgQRCodeForm/';
			$config['errorlog']		= './assets/imgQRCodeForm/';
			$config['imagedir']		= './assets/imgQRCodeForm/';
			$config['quality']		= true;
			$config['size']			= '1024';
			$config['black']		= array(224,255,255);
			$config['white']		= array(0,0,128);//untuk ubah warna di libralies/qrcode/qrimage.php white default 0,0,0
			$this->ciqrcode->initialize($config);

			$imgName = base64_encode("4ndh1k42023").'.jpg';

			$params['data'] = $isi_teks;
			$params['level'] = 'H'; //H=High
			$params['size'] = 5;
			$params['savename'] = FCPATH.$config['imagedir'].$imgName; //simpan image QR CODE ke folder assets/images/
			$params['logo'] = "./assets/img/andhika.png";

			$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE

			$fileGen = base_url('assets/imgQRCodeForm/'.base64_encode('4ndh1k42023').'.jpg');
		}

		print json_encode($fileGen);
	}
	function getDataEdit()
	{
		$idEdit = $_POST['idEdit'];

		$dataOut['rsl'] = $this->myapp->getDataQueryDB6("SELECT * FROM qrcode WHERE st_delete = '0' AND id = '".$idEdit."'");

		print json_encode($dataOut);
	}
	function delFile()
	{
		$idDel = $_POST['id'];
		$stData = "";

		try {
				$valData['st_delete'] = "1";
				$whereNya = "id = '".$idDel."'";
				$this->myapp->updateDataDb6("qrcode",$valData,$whereNya);

				$stData = "Delete Success..!!";
			} catch (Exception $e) {
				$stData = "Failed =>".$e;
			}
		print json_encode($stData);
	}
































}