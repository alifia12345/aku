<?php 
   
   require "koneksidb.php";

   $ambilrfid	 = $_GET["rfid"];
   $ambilnamatol = $_GET["namatol"];
   
   date_default_timezone_set('Asia/Jakarta');
   $tgl=date("Y-m-d G:i:s");

    //$data = query("SELECT * FROM tabel_monitoring")[0];
    
    //MENGAMBIL DATA NAMA
    $nama = query("SELECT *FROM tb_daftarrfid WHERE rfid = '$ambilrfid'")[0];
    $nama1 = $nama['nama'];
    
    //MENGAMBIL DATA ALAMAT
    $alamat = query("SELECT *FROM tb_daftarrfid WHERE rfid = '$ambilrfid'")[0];
    $alamat1 = $alamat['alamat'];
    
    //MENGAMBIL DATA TELEPON
    $telepon = query("SELECT *FROM tb_daftarrfid WHERE rfid = '$ambilrfid'")[0];
    $telepon1 = $telepon['telepon'];
    
		// UPDATE DATA REALTIME PADA TABEL tb_monitoring
		$sql      = "UPDATE tb_monitoring SET tanggal	= '$tgl', rfid	= '$ambilrfid'";
		$koneksi->query($sql);
			
		//INSERT DATA REALTIME PADA TABEL tb_save  	
    $datarfid = mysqli_query($koneksi, "SELECT * FROM tb_daftarrfid WHERE rfid = '$ambilrfid'");
    $rowrfid  = mysqli_fetch_array($datarfid);
    
    $datatol = mysqli_query($koneksi, "SELECT * FROM tb_tol WHERE namatol = '$ambilnamatol'");
    $rowtol  = mysqli_fetch_array($datatol);
    
    $tambahsaldo = $rowrfid['saldoawal'] - $rowtol['bayar'];	
 
		$sqlsave = "INSERT INTO tb_simpan (tanggal, rfid, nama, alamat, telepon, saldoawal, bayar, saldoakhir, namatol) VALUES ('" . $tgl . "', '" . $ambilrfid . "' , '" . $nama1 . "', '" . $alamat1 . "' , '" . $telepon1 . "', '" . $rowrfid['saldoawal'] . "', '" . $rowtol['bayar'] . "' , '" . $tambahsaldo . "', '" . $ambilnamatol . "')";
		$koneksi->query($sqlsave);

		//MENJADIKAN JSON DATA
		//$response = query("SELECT * FROM tb_monitoring")[0];
		$response = query("SELECT * FROM tb_daftarrfid,tb_monitoring WHERE tb_daftarrfid.rfid='$ambilrfid'" )[0];
      	$result = json_encode($response);
     	echo $result;



 ?>