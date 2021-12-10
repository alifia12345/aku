<?php 
 error_reporting(0);
   require "koneksidb.php";

   $ambilrfid	 = $_GET["rfid"];
   $ambilnamatol = $_GET["namatol"];
   
   date_default_timezone_set('Asia/Jakarta');
   $tgl=date("Y-m-d G:i:s");


    
    // UPDATE DATA REALTIME PADA TABEL tb_monitoring
		$sql      = "UPDATE tb_monitoring SET tanggal	= '$tgl', rfid	= '$ambilrfid', namatol = '$ambilnamatol', status= 'belumterdaftar'";
		$koneksi->query($sql);
    
    //MENGAMBIL DATA NAMA
    $result = query("SELECT *FROM tb_daftarrfid WHERE rfid = '$ambilrfid'")[0];
    $nama1 = $result['nama'];
    
    //MENGAMBIL DATA ALAMAT
    $alamat1 = $result['alamat'];
    
    //MENGAMBIL DATA TELEPON
    $telepon1 = $result['telepon'];
    
    //AMBIL SALDO
    $bayar1 = $result['saldoawal'];
    
    
    //MENGAMBIL DATA harga tol
    $tbtol = query("SELECT bayar FROM tb_tol WHERE tb_tol.namatol='$ambilnamatol'")[0];
    $bayar = $tbtol['bayar'];
    
    //LOGIKA
    $total = $bayar1 - $bayar;
    
    $rfidcek = $result['rfid'];
    
    if($rfidcek != null){
    
    if($bayar1 >= $bayar){
   // UPDATE DATA REALTIME PADA TABEL tb_daftarrfid
		$sql      = "UPDATE tb_daftarrfid SET saldoawal	= '$total' WHERE rfid	= '$ambilrfid'";
		$koneksi->query($sql);
    	
		//INSERT DATA REALTIME PADA TABEL tb_save  	
    $sql      = "UPDATE tb_monitoring SET tanggal	= '$tgl', rfid	= '$ambilrfid', namatol = '$ambilnamatol', status= 'berhasil'";
		$koneksi->query($sql);

		$sqlsave = "INSERT INTO tb_simpan (tanggal, rfid, nama, alamat, telepon, saldoawal, bayar, saldoakhir, namatol) VALUES ('" . $tgl . "', '" . $ambilrfid . "' , '" . $nama1 . "', '" . $alamat1 . "' , '" . $telepon1 . "', '" . $bayar1 . "', '" . $bayar . "' , '" . $total. "', '" . $ambilnamatol . "')";
		$koneksi->query($sqlsave);
   
   }else{
     $sql      = "UPDATE tb_monitoring SET tanggal	= '$tgl', rfid	= '$ambilrfid', namatol = '$ambilnamatol', status= 'gagal'";
		 $koneksi->query($sql);
    
   }
   
   

		//MENJADIKAN JSON DATA
		//$response = query("SELECT * FROM tb_monitoring")[0];
	 	$response = query("SELECT * FROM tb_daftarrfid,tb_monitoring WHERE tb_daftarrfid.rfid='$ambilrfid'" )[0];
 	  $result = json_encode($response);
   	echo $result;
    
    }



 ?>