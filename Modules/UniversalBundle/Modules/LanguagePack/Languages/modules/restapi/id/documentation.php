<?php 
return [
  'title' => 'Referensi API',
  'subtitle' => 'Dokumentasi Pengembang',
  'intro' => 'Selamat datang di referensi API lengkap. Sistem ini memungkinkan integrasi mendalam dengan backend POS, memungkinkan Anda membuat aplikasi pelayan, kios pelanggan, atau dasbor khusus.',
  'search_placeholder' => 'Titik akhir pencarian...',
  'base_url' => 'URL dasar',
  'auth_header' => 'Otentikasi',
  'auth_desc' => 'Otentikasi melalui Token Pembawa. Sertakan `Otorisasi: Pembawa <token>` di header.',
  'sections' => [
    'auth' => 'Otentikasi',
    'platform' => 'Platform',
    'resources' => 'Sumber daya',
    'customers' => 'Pelanggan',
    'catalog' => 'Katalog',
    'sales' => 'Penjualan & Pesanan',
    'kot' => 'Pesanan Dapur (KOT)',
    'delivery' => 'Manajemen Pengiriman',
    'operations' => 'Operasi',
    'hardware' => 'Perangkat Keras & Perangkat',
    'pusher' => 'Waktu Nyata & Dorong',
  ],
  'endpoints' => [
    'login' => [
      'title' => 'Login',
      'desc' => 'Dapatkan token akses.',
    ],
    'me' => [
      'title' => 'Profil Pengguna',
      'desc' => 'Dapatkan pengguna & izin saat ini.',
    ],
    'config' => [
      'title' => 'Konfigurasi & Fitur',
      'desc' => 'Dapatkan pengaturan sistem, tanda fitur, dan modul aktif.',
    ],
    'permissions' => [
      'title' => 'Izin',
      'desc' => 'Buat daftar peran dan kemampuan pengguna.',
    ],
    'printers' => [
      'title' => 'pencetak',
      'desc' => 'Dapatkan printer tanda terima/KOT yang dikonfigurasi.',
    ],
    'receipts' => [
      'title' => 'Pengaturan Tanda Terima',
      'desc' => 'Dapatkan konfigurasi penataan tanda terima.',
    ],
    'switch_branch' => [
      'title' => 'Beralih Cabang',
      'desc' => 'Ubah konteks cabang aktif.',
    ],
    'langs' => [
      'title' => 'Bahasa',
      'desc' => 'Dapatkan bahasa yang tersedia.',
    ],
    'currencies' => [
      'title' => 'Mata uang',
      'desc' => 'Dapatkan mata uang sistem.',
    ],
    'gateways' => [
      'title' => 'Gerbang Pembayaran',
      'desc' => 'Dapatkan kredensial gateway publik.',
    ],
    'staff' => [
      'title' => 'Daftar Staf',
      'desc' => 'Dapatkan semua anggota staf.',
    ],
    'roles' => [
      'title' => 'Peran',
      'desc' => 'Dapatkan peran pengguna yang tersedia.',
    ],
    'areas' => [
      'title' => 'Daerah',
      'desc' => 'Dapatkan area denah lantai.',
    ],
    'addr_list' => [
      'title' => 'Daftar Alamat',
      'desc' => 'Dapatkan alamat untuk pelanggan.',
    ],
    'addr_create' => [
      'title' => 'Buat Alamat',
      'desc' => 'Tambahkan alamat pengiriman baru.',
    ],
    'addr_update' => [
      'title' => 'Perbarui Alamat',
      'desc' => 'Ubah alamat yang ada.',
    ],
    'addr_delete' => [
      'title' => 'Hapus Alamat',
      'desc' => 'Hapus alamat.',
    ],
    'menus' => [
      'title' => 'Menu',
      'desc' => 'Dapatkan menu aktif.',
    ],
    'categories' => [
      'title' => 'Kategori',
      'desc' => 'Dapatkan kategori item.',
    ],
    'items' => [
      'title' => 'Semua Barang',
      'desc' => 'Dapatkan katalog item lengkap dengan harga & pengubah.',
    ],
    'items_filter' => [
      'title' => 'Saring Item',
      'desc' => 'Dapatkan item berdasarkan kategori atau menu.',
    ],
    'variations' => [
      'title' => 'Variasi Barang',
      'desc' => 'Dapatkan variasi untuk item tertentu.',
    ],
    'modifiers' => [
      'title' => 'Pengubah Barang',
      'desc' => 'Dapatkan grup pengubah untuk item tertentu.',
    ],
    'orders_create' => [
      'title' => 'Kirim Pesanan',
      'desc' => 'Buat pesanan baru (Makan di Tempat/Pengantaran).',
    ],
    'orders_list' => [
      'title' => 'Daftar Pesanan',
      'desc' => 'Dapatkan riwayat pesanan.',
    ],
    'orders_detail' => [
      'title' => 'Detil Pesanan',
      'desc' => 'Dapatkan objek pesanan penuh.',
    ],
    'orders_status' => [
      'title' => 'Perbarui Status',
      'desc' => 'Ubah status pesanan (misalnya siap).',
    ],
    'orders_pay' => [
      'title' => 'Bayar Pesanan',
      'desc' => 'Catat pembayaran dan tutup pesanan.',
    ],
    'order_number' => [
      'title' => 'Nomor Pratinjau',
      'desc' => 'Dapatkan nomor pesanan berikutnya.',
    ],
    'order_types' => [
      'title' => 'Jenis Pesanan',
      'desc' => 'Dapatkan jenisnya (Makan di Tempat, Bawa Pulang).',
    ],
    'actions' => [
      'title' => 'Tindakan yang Diizinkan',
      'desc' => 'Dapatkan tindakan pesanan yang valid (kot, bill).',
    ],
    'platforms' => [
      'title' => 'Platform Pengiriman',
      'desc' => 'Dapatkan platform pihak ketiga.',
    ],
    'charges' => [
      'title' => 'Biaya Tambahan',
      'desc' => 'Dapatkan biaya/biaya layanan.',
    ],
    'taxes' => [
      'title' => 'Pajak',
      'desc' => 'Dapatkan tarif pajak yang dikonfigurasi.',
    ],
    'tables' => [
      'title' => 'Tabel',
      'desc' => 'Dapatkan status tabel waktu nyata.',
    ],
    'unlock' => [
      'title' => 'Buka Kunci Tabel',
      'desc' => 'Paksa membuka kunci meja.',
    ],
    'res_today' => [
      'title' => 'Reservasi Hari Ini',
      'desc' => 'Dapatkan reservasi untuk dashboard.',
    ],
    'res_list' => [
      'title' => 'Semua Reservasi',
      'desc' => 'Dapatkan reservasi dengan nomor halaman.',
    ],
    'res_create' => [
      'title' => 'Buat Reservasi',
      'desc' => 'Pesan meja.',
    ],
    'res_status' => [
      'title' => 'Perbarui Reservasi',
      'desc' => 'Ubah status reservasi.',
    ],
    'cust_search' => [
      'title' => 'Cari Pelanggan',
      'desc' => 'Temukan berdasarkan nama/telepon.',
    ],
    'cust_save' => [
      'title' => 'Selamatkan Pelanggan',
      'desc' => 'Buat atau perbarui profil.',
    ],
    'waiters' => [
      'title' => 'Pelayan',
      'desc' => 'Dapatkan staf dengan peran pelayan/sopir.',
    ],
    'kot_list' => [
      'title' => 'Daftar KOT',
      'desc' => 'Dapatkan tiket pesanan dapur untuk dipajang.',
    ],
    'kot_detail' => [
      'title' => 'Detil KOT',
      'desc' => 'Dapatkan KOT tunggal dengan item.',
    ],
    'kot_create' => [
      'title' => 'Buat KOT',
      'desc' => 'Buat KOT baru untuk pesanan yang sudah ada.',
    ],
    'kot_status' => [
      'title' => 'Perbarui Status KOT',
      'desc' => 'Ubah status KOT (in_kitchen, food_ready, serve, cancelled).',
    ],
    'kot_item_status' => [
      'title' => 'Perbarui Status Barang',
      'desc' => 'Perbarui status masing-masing item (memasak, siap, dibatalkan).',
    ],
    'kot_places' => [
      'title' => 'Tempat Dapur',
      'desc' => 'Dapatkan stasiun/tempat dapur.',
    ],
    'kot_cancel_reasons' => [
      'title' => 'Alasan Pembatalan',
      'desc' => 'Dapatkan alasan pembatalan KOT.',
    ],
    'order_kots' => [
      'title' => 'Pesan KOT',
      'desc' => 'Dapatkan semua KOT untuk pesanan tertentu.',
    ],
    'delivery_settings' => [
      'title' => 'Pengaturan Pengiriman',
      'desc' => 'Dapatkan konfigurasi pengiriman cabang (radius, biaya, jadwal).',
    ],
    'delivery_fee_calc' => [
      'title' => 'Hitung Biaya',
      'desc' => 'Hitung biaya pengiriman berdasarkan lokasi pelanggan.',
    ],
    'delivery_fee_tiers' => [
      'title' => 'Tingkatan Biaya',
      'desc' => 'Dapatkan tingkatan biaya berdasarkan jarak.',
    ],
    'delivery_platforms_list' => [
      'title' => 'Daftar Platform',
      'desc' => 'Dapatkan platform pengiriman aktif (Uber Eats, dll.).',
    ],
    'delivery_platform_get' => [
      'title' => 'Detil Peron',
      'desc' => 'Dapatkan platform pengiriman tunggal dengan info komisi.',
    ],
    'delivery_platform_create' => [
      'title' => 'Buat Platform',
      'desc' => 'Tambahkan platform pengiriman baru.',
    ],
    'delivery_platform_update' => [
      'title' => 'Perbarui Platform',
      'desc' => 'Ubah pengaturan/komisi platform.',
    ],
    'delivery_platform_delete' => [
      'title' => 'Hapus Platform',
      'desc' => 'Hapus atau nonaktifkan platform pengiriman.',
    ],
    'delivery_exec_list' => [
      'title' => 'Daftar Eksekutif',
      'desc' => 'Dapatkan staf pengiriman dengan filter status.',
    ],
    'delivery_exec_create' => [
      'title' => 'Buat Eksekutif',
      'desc' => 'Tambahkan eksekutif pengiriman baru.',
    ],
    'delivery_exec_update' => [
      'title' => 'Perbarui Eksekutif',
      'desc' => 'Ubah info eksekutif pengiriman.',
    ],
    'delivery_exec_delete' => [
      'title' => 'Hapus Eksekutif',
      'desc' => 'Hapus atau nonaktifkan eksekutif pengiriman.',
    ],
    'delivery_exec_status' => [
      'title' => 'Status Eksekutif',
      'desc' => 'Perbarui ketersediaan (tersedia/on_delivery/tidak aktif).',
    ],
    'delivery_assign' => [
      'title' => 'Tetapkan Pengiriman',
      'desc' => 'Tetapkan eksekutif/platform untuk memesan.',
    ],
    'delivery_order_status' => [
      'title' => 'Status Pengiriman',
      'desc' => 'Perbarui status pengiriman pesanan (persiapan, keluar_untuk_pengiriman, terkirim).',
    ],
    'delivery_orders' => [
      'title' => 'Pesanan Pengiriman',
      'desc' => 'Dapatkan daftar pesanan pengiriman yang difilter.',
    ],
    'multipos_reg' => [
      'title' => 'Daftarkan Perangkat',
      'desc' => 'Tautkan perangkat keras fisik.',
    ],
    'multipos_check' => [
      'title' => 'Periksa Perangkat',
      'desc' => 'Verifikasi pendaftaran.',
    ],
    'notif_token' => [
      'title' => 'Daftarkan FCM',
      'desc' => 'Simpan token dorong.',
    ],
    'notif_list' => [
      'title' => 'Pemberitahuan',
      'desc' => 'Dapatkan peringatan dalam aplikasi.',
    ],
    'notif_read' => [
      'title' => 'Tandai Baca',
      'desc' => 'Tutup pemberitahuan.',
    ],
    'pusher_settings' => [
      'title' => 'Dapatkan Pengaturan Pendorong',
      'desc' => 'Ambil konfigurasi Pusher lengkap. Dapat diakses oleh semua pengguna yang diautentikasi (superadmin, admin, staf).',
    ],
    'pusher_broadcast' => [
      'title' => 'Dapatkan Pengaturan Siaran',
      'desc' => 'Dapatkan konfigurasi siaran real-time Pusher. Pengaturan seluruh sistem untuk semua pengguna.',
    ],
    'pusher_beams' => [
      'title' => 'Dapatkan Pengaturan Balok',
      'desc' => 'Dapatkan konfigurasi pemberitahuan push Pusher Beams. Dapat diakses oleh semua pengguna yang diautentikasi.',
    ],
    'pusher_status' => [
      'title' => 'Periksa Status Pendorong',
      'desc' => 'Pemeriksaan status cepat untuk memverifikasi apakah layanan Pusher diaktifkan. Tersedia untuk semua pengguna.',
    ],
    'pusher_authorize' => [
      'title' => 'Otorisasi Saluran',
      'desc' => 'Otorisasi akses pengguna ke saluran pribadi dan kehadiran. Membutuhkan otentikasi yang valid.',
    ],
    'pusher_presence' => [
      'title' => 'Dapatkan Anggota Kehadiran',
      'desc' => 'Ambil daftar pengguna yang saat ini terhubung ke saluran kehadiran. Data seluruh sistem.',
    ],
  ],
];