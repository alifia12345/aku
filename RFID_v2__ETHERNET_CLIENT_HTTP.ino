//#include <LiquidCrystal_I2C.h>
#include <SPI.h>
#include <Ethernet.h>
#include <ArduinoJson.h> //6.17.3
#include <SPI.h>
#include <MFRC522.h>
#include <Servo.h>
//LiquidCrystal_I2C lcd(0x27, 16, 2);

// replace the MAC address below by the MAC address printed on a sticker on the Arduino Shield 2
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };

EthernetClient client;

int    HTTP_PORT   = 80;
String HTTP_METHOD = "GET";
char   HOST_NAME[] = "192.168.1.51"; // change to your PC's IP address
String PATH_NAME   = "/rfidui/data-api.php";
String getData;
String namatol = "sukabumi";

#define SS_PIN 9
#define RST_PIN 8
#define buzzer 7

MFRC522 mfrc522(SS_PIN, RST_PIN);   // Create MFRC522 instance.

int kunci = 0;
int tombol = 5;
int servo = 6;
Servo myservo;

void setup() {
  Serial.begin(115200);
  // lcd.begin();
  myservo.attach(servo);
  pinMode (buzzer, OUTPUT);
  //  pinMode(IR, INPUT);
  while (!Serial);
  SPI.begin();      // Initiate  SPI bus
  mfrc522.PCD_Init();   // Initiate MFRC522
  //delay(3000);
  //START IP DHCP
  Serial.println("Konfigurasi DHCP, Silahkan Tunggu!");
  if (Ethernet.begin(mac) == 0) {
    Serial.println("DHCP Gagal!");
    if (Ethernet.hardwareStatus() == EthernetNoHardware) {
      Serial.println("Ethernet Tidak tereteksi :(");
    } else if (Ethernet.linkStatus() == LinkOFF) {
      Serial.println("Hubungkan kabel Ethernet!");
    }
    while (true) {
      delay(1);
    }
  }
  //End DHCP
  delay(5000);
  Serial.print("IP address: ");
  Serial.println(Ethernet.localIP());
  client.connect(HOST_NAME, HTTP_PORT);
  Serial.println("Siap Digunakan!");
  myservo.write(90);
}

void loop() {

  if (kunci == 0) {
    //Baca data
    //Program yang akan dijalankan berulang-ulang
    if ( ! mfrc522.PICC_IsNewCardPresent()) {
      return;
    }
    // Select one of the cards
    if ( ! mfrc522.PICC_ReadCardSerial()) {
      return;
    }
    //  lcd.clear();
    //  lcd.setCursor(0, 0); lcd.print("Selamat Datang");
    //lcd.setCursor(0, 1); lcd.print("Silahkan Masuk");
    delay(1000);

    //Show UID on serial monitor
    Serial.print("UID tag :");
    String uidString;
    byte letter;
    for (byte i = 0; i < mfrc522.uid.size; i++)
    {
      uidString.concat(String(mfrc522.uid.uidByte[i] < 0x10 ? "" : ""));
      uidString.concat(String(mfrc522.uid.uidByte[i], HEX));
    }
    Serial.print("Message : ");
    uidString.toUpperCase();
    Serial.println(uidString);
    digitalWrite(buzzer, HIGH);
    delay(100);
    digitalWrite(buzzer, LOW);

    //POST TO WEB
    client.connect(HOST_NAME, HTTP_PORT);
    client.println(HTTP_METHOD + " " + PATH_NAME +
                   "?rfid=" + String(uidString) +
                   "&namatol=" + String(namatol) +
                   //"&sensor1=" + String(sensor1) +
                   //"&sensor2=" + String(sensor2) +
                   " HTTP/1.1");
    client.println("Host: " + String(HOST_NAME));
    client.println("Connection: close");
    client.println(); // end HTTP header

    while (client.connected()) {
      if (client.available()) {
        char endOfHeaders[] = "\r\n\r\n";
        client.find(endOfHeaders);
        getData = client.readString();
        getData.trim();
        //Serial.println (getData);
        //AMBIL DATA JSON
        const size_t capacity = JSON_OBJECT_SIZE(9) + 160; //cari dulu nilainya pakai Arduino Json 5 Asisten
        DynamicJsonDocument doc(capacity);
        //StaticJsonDocument<192> doc;
        DeserializationError error = deserializeJson(doc, getData);

        const char* id_dibaca       = doc["id"];
        const char* rfid_dibaca     = doc["rfid"];
        const char* nama_dibaca     = doc["nama"];
        const char* alamat_dibaca   = doc["alamat"];
        const char* telepon_dibaca  = doc["telepon"];
        const char* tanggal_dibaca  = doc["tanggal"];
        const char* saldoawal_dibaca = doc["saldoawal"];
        const char* namatol_dibaca  = doc["namatol"];
        const char* status_dibaca   = doc["status"];


        //LOGIKA
        //      int readIR = digitalRead(IR);

        if (String(nama_dibaca) != "" && String(status_dibaca) == "berhasil") {
          Serial.println("Kartu Terdaftar!");
          //POST TO SERIAL
          Serial.print("ID       = "); Serial.println(id_dibaca);
          Serial.print("RFID     = "); Serial.println(rfid_dibaca);
          Serial.print("Nama     = "); Serial.println(nama_dibaca);
          Serial.print("Alamat   = "); Serial.println(alamat_dibaca);
          Serial.print("Telepon  = "); Serial.println(telepon_dibaca);
          Serial.print("Waktu    = "); Serial.println(tanggal_dibaca);
          Serial.print("saldoawal= "); Serial.println(saldoawal_dibaca);
          Serial.print("namatol  = "); Serial.println(namatol_dibaca);
          Serial.println();
          //        lcd.clear();
          //        lcd.setCursor(1, 0); lcd.print("BERHASIL MASUK");
          myservo.write(180);
          delay(1000);
          buzzeroke();
          kunci = 1;
        } else if (String(nama_dibaca) != "" && String(status_dibaca) == "gagal") {
          buzzergagal();
          Serial.println("Saldo Tidak Cukup!");
        } else {
          buzzergagal();
          Serial.println("Kartu Tidak terdaftar!");
          //        lcd.clear();
          //        lcd.setCursor(1, 0); lcd.print("TIDAK TERDAFTAR");
        }
      }
    }
  }
  int bacatombol = digitalRead(tombol);
  if (bacatombol == 1 && kunci == 1) {
    myservo.write(90);
    Serial.print("Tap Kartu");
    kunci = 0;
  }
}



void buzzeroke() {
  digitalWrite(buzzer, HIGH);
  delay(100);
  digitalWrite(buzzer, LOW);
  delay(100);
  digitalWrite(buzzer, HIGH);
  delay(100);
  digitalWrite(buzzer, LOW);
  delay(100);
}

void buzzergagal() {
  digitalWrite(buzzer, HIGH);
  delay(1000);
  digitalWrite(buzzer, LOW);
  delay(10);
}
