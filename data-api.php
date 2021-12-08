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
    
    //UPDATE DATA
    $sql  ="UPDATE tb_daftarrfid SET namatol='namatol'";
    $koneksi->query($sql);
    
    //MENGAMBIL DATA harga tol
    $tbtol = query("SELECT bayar FROM tb_tol WHERE tb_tol.namatol='$ambilnamatol'")[0];
    $bayar = $tbtol['bayar'];
    
    //AMBIL SALDO
    $tbtol1 = query("SELECT saldoawal FROM tb_daftarrfid WHERE rfid='$ambilrfid'")[0];
    $bayar1 = $tbtol1['saldoawal'];
    
    //LOGIKA
    $total = $bayar1 - $bayar;
    
   // UPDATE DATA REALTIME PADA TABEL tb_daftarrfid
		$sql      = "UPDATE tb_daftarrfid SET saldoawal	= '$total' WHERE rfid	= '$ambilrfid'";
		$koneksi->query($sql);
    
		// UPDATE DATA REALTIME PADA TABEL tb_monitoring
		$sql      = "UPDATE tb_monitoring SET tanggal	= '$tgl', rfid	= '$ambilrfid'";
		$koneksi->query($sql);
			
		//INSERT DATA REALTIME PADA TABEL tb_save  	
  

		$sqlsave = "INSERT INTO tb_simpan (tanggal, rfid, nama, alamat, telepon, saldoawal, bayar, saldoakhir, namatol) VALUES ('" . $tgl . "', '" . $ambilrfid . "' , '" . $nama1 . "', '" . $alamat1 . "' , '" . $telepon1 . "', '" . $bayar1 . "', '" . $bayar . "' , '" . $total. "', '" . $ambilnamatol . "')";
		$koneksi->query($sqlsave);

		//MENJADIKAN JSON DATA
		//$response = query("SELECT * FROM tb_monitoring")[0];
	 	$response = query("SELECT * FROM tb_daftarrfid,tb_monitoring WHERE tb_daftarrfid.rfid='$ambilrfid'" )[0];
 
 	  $result = json_encode($response);
   	echo $result;



 ?>