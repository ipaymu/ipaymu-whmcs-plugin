# WHMCS Payment Gateway iPaymu
iPaymu hadir dengan plugin untuk membantu Anda menerima pembayaran melalui iPaymu.

### Langkah-langkah yang perlu Anda Integrasikan
- Unduh dan instal pluginnya.
- Jika Anda belum memiliki akun iPaymu maka Anda perlu - mendaftar.
- Masuk menu API pada dashboard iPaymu, disana Anda akan mendapatkan VA dan Apikey yang nantinya akan dibutuhkan konfigurasi

### Instalasi
Beberapa langkah yang harus dilakukan secara manual yaitu memindahkan file-file yang telah didownload sebelumnya: 

1. Memindahkan isi folder "callback" pada folder instalasi whmcs `` /modules/gateways/callback ``

2. Memindahkan isi folder folder "ipaymu" dan file "ipaymu.php" pada folder instalasi whmcs
`` /modules/gateways ``

3. Melakukan aktivasi pada menu "App & Integration", lalu pilih menu "Browse" pada navigasi sebelah kiri pilih menu "Payments"

4. Cari plugin dengan kata kunci "iPaymu Direct Payment" dan klik lalu aktifkan, 

5. Konfigurasi environment iPaymu yang sebelumnya telah didapatkan melalui dashboard iPaymu, 

6. Plugin siap digunakan

### Detail
iPaymu memberikan kemudahan transaksi dengan 26 channel pembayaran. 
Terkait keamanan, Anda tidak perlu khawatir karena iPaymu telah mendapat sesuai dengan standar PCI DSS. Selain itu, sistem pembayaran dilengkapi dengan enkripsi menyeluruh.
Informasi selengkapnya <a href="www.ipaymu.com"> www.ipaymu.com</a>
