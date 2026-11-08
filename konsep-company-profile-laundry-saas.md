# Product Requirements Document (PRD)
# Company Profile & Landing Page — Laundry SaaS

**Versi dokumen:** 1.0 (draft konsep)  
**Tanggal:** 31 Oktober 2026  
**Status:** Siap dijadikan acuan desain dan implementasi; nama brand, harga, trial, dan konten legal perlu dikonfirmasi.  
**Stack aplikasi yang sudah ada:** Laravel, Blade, TailwindCSS  
**Target pelanggan:** Pemilik usaha laundry kiloan, satuan, dan laundry skala kecil–menengah di Indonesia.

> **Prinsip utama:** Website ini adalah mesin akuisisi pelanggan SaaS, bukan toko source code dan bukan halaman promosi tugas akademik. Pengunjung harus memahami manfaatnya dan dapat mendaftar serta masuk ke aplikasi yang sudah tersedia.

## 1. Ringkasan produk

Aplikasi laundry sudah mendukung registrasi dan akses dashboard. Langkah berikutnya adalah membuat website publik sebagai pintu masuk bisnis: menjelaskan produk, memperlihatkan fitur nyata, menjawab keberatan pembeli, menawarkan paket langganan, dan mengarahkan pengunjung ke alur registrasi.

### 1.1 Tujuan

1. Mendapatkan pendaftaran usaha laundry yang relevan.
2. Meningkatkan kepercayaan melalui demonstrasi fitur yang benar-benar tersedia.
3. Menjelaskan biaya dan cara kerja layanan secara transparan.
4. Menghubungkan landing page dengan login, registrasi, dan billing SaaS yang sudah tersedia atau masih dikembangkan.
5. Mendukung akses mudah dari ponsel, karena banyak pemilik laundry menggunakan WhatsApp dan browser mobile.

### 1.2 Positioning

**Kategori:** Software manajemen operasional laundry berbasis web, berlangganan.  
**Janji nilai:** Mengelola pesanan, memantau proses cucian, mencatat pembayaran, dan melihat laporan dari satu sistem.  
**Keunggulan yang bisa ditonjolkan setelah pengujian:** Tiga role operasional, tracking pesanan publik, pembayaran DP/pelunasan, pengelolaan rak, dan ekspor laporan.

**Usulan tagline:** **"Kelola Laundry Lebih Mudah, Bisnis Lebih Tertata."**

Gunakan nama brand sementara `[NAMA_BRAND]` sampai nama bisnis dan domain dipastikan. Jangan memakai nama/logo pesaing atau mengadaptasi copy mereka secara langsung.

## 2. Inspirasi kompetitor & diferensiasi

Referensi:
- https://sistemlaundry.com/ — alur problem–solution–feature–pricing–FAQ, CTA trial, penekanan automasi WhatsApp.
- https://laundrygo.id/ — tampilan produk yang kuat, cerita manfaat untuk pemilik usaha, section cara kerja, dan trust signals.

**Diferensiasi yang ditargetkan:** sederhana untuk pemilik laundry kecil, mudah mulai tanpa instalasi lokal, alur kerja jelas dari kasir hingga pengambilan, status pesanan dapat ditelusuri, dan harga transparan. Tidak mengklaim integrasi WhatsApp otomatis, aplikasi native, mesin IoT, multi-outlet, ataupun pembayaran QRIS laundry jika belum diimplementasikan dan diuji.

## 3. Sasaran audiens & pesan

| Audiens | Masalah | Pesan yang ditonjolkan | CTA |
|---|---|---|---|
| Pemilik laundry baru | Catatan manual, order mulai bertambah | Mulai menata pesanan dan pelanggan sejak awal | Daftar Sekarang |
| Pemilik laundry yang sudah berjalan | Sulit memantau pekerjaan, pembayaran, laporan | Pantau operasional dan posisi cucian dari satu dashboard | Lihat Cara Kerja |
| Pemilik laundry dengan staf | Tugas kasir dan operator tercampur | Hak akses staf dan alur kerja yang terstruktur | Lihat Fitur |

**Nada bahasa:** profesional, ramah, ringkas, mudah dimengerti pemilik usaha non-teknis. Hindari jargon SaaS, tenant, atau middleware di halaman konsumen.

## 4. Site map / arsitektur informasi

```text
/                       Landing Page
/fitur                  Detail fitur (opsional fase 2)
/harga                  Harga & paket (opsional: anchor /#harga pada MVP)
/tentang-kami           Profil produk / tim / visi
/kontak                 Kontak dan formulir konsultasi
/faq                    FAQ (opsional: anchor /#faq pada MVP)
/kebijakan-privasi      Kebijakan privasi — perlu peninjauan legal
/syarat-layanan         Syarat layanan — perlu peninjauan legal
/login                  Login aplikasi yang sudah ada
/register               Registrasi aplikasi yang sudah ada
/dashboard              Dashboard (route sebenarnya sesuaikan repository)
```

Prioritas MVP: landing page satu halaman dengan navigasi anchor, halaman legal, kontak, login dan register. Halaman fitur/harga terpisah boleh dibuat setelah ada kebutuhan SEO dan konten yang cukup.

## 5. Struktur landing page (urutan yang direkomendasikan)

### Section 01 — Navbar

**Logo:** `[NAMA_BRAND]`  
**Menu desktop:** Beranda | Fitur | Cara Kerja | Harga | FAQ | Kontak  
**Tombol kanan:** Masuk (sekunder) | Mulai Sekarang (primer)  
**Mobile:** hamburger menu, CTA tetap mudah ditemukan. Navbar sticky tetapi tidak menutup konten.

Perilaku ketika login: tombol `Masuk` diganti `Buka Dashboard` jika sesi aktif; CTA daftar tidak ditampilkan kepada pengguna yang sudah punya usaha/akun, jika keadaan autentikasi dapat ditentukan dengan aman.

### Section 02 — Hero

**Eyebrow:** SISTEM MANAJEMEN LAUNDRY ONLINE

**Headline utama (H1):**
> Kelola Laundry Lebih Mudah, Bisnis Lebih Tertata.

**Subheadline:**
> Catat pesanan, pantau proses cucian, kelola pembayaran, dan lihat laporan usaha dalam satu aplikasi. Bisa diakses melalui browser tanpa instalasi software yang rumit.

**CTA primer:** `Mulai Sekarang` → `/register`  
**CTA sekunder:** `Lihat Cara Kerja` → `/#cara-kerja`  
**Visual:** tangkapan layar asli dashboard, idealnya disertai preview alur order. Tampilkan UI di frame desktop dan mobile jika responsive telah diverifikasi.

**Microcopy opsional:** `Pendaftaran online • Bisa diakses dari HP dan laptop`. Gunakan `Coba Gratis 14 Hari` hanya jika trial 14 hari benar-benar sudah aktif.

### Section 03 — Masalah nyata pemilik laundry

**Judul:** Masih Mengelola Laundry dengan Catatan Terpisah?

Tiga kartu masalah:
1. **Order sulit dilacak** — catatan kertas atau chat membuat pencarian riwayat lama.
2. **Status cucian tidak jelas** — kasir harus berulang kali bertanya kepada operator.
3. **Pencatatan pembayaran merepotkan** — pembayaran DP, sisa tagihan, dan laporan perlu direkap manual.

**Penutup:** `[NAMA_BRAND]` membantu membuat proses tersebut lebih terstruktur dalam satu alur kerja.

Jangan menyebut persentase peningkatan produktivitas atau penghematan waktu tanpa hasil pengukuran nyata.

### Section 04 — Keunggulan utama (3–4 kartu)

**Judul:** Semua yang Dibutuhkan untuk Mengelola Laundry dalam Satu Tempat

- **Order & Pelanggan:** Pencatatan order dengan multi-layanan dan riwayat pelanggan.
- **Alur Produksi:** Tahap diterima, dicuci, dikeringkan, disetrika, hingga siap diambil.
- **Pembayaran & Laporan:** DP, pelunasan, laporan bisnis, ekspor PDF dan Excel sesuai fitur yang telah diuji.
- **Tracking Pesanan:** Pelanggan dapat memeriksa status melalui nomor order pada halaman pelacakan.

Tampilkan icon konsisten dan cuplikan UI asli. Jika fitur tertentu belum berjalan pada build SaaS, jangan memuatnya di landing page publik.

### Section 05 — Showcase aplikasi

**Judul:** Lihat Bagaimana Sistem Bekerja

Gunakan tab atau carousel dengan screenshot asli:
1. Dashboard admin / ringkasan operasional.
2. Halaman kasir / buat order.
3. Halaman operator / perubahan status.
4. Halaman tracking publik.
5. Halaman pembayaran DP / pelunasan.
6. Halaman laporan.

Tiap screenshot memakai judul dan manfaat satu kalimat. Semua data di screenshot harus dummy.

**Aset:** `hero-dashboard.webp`, `screen-kasir.webp`, `screen-operator.webp`, `screen-tracking.webp`, `screen-pembayaran.webp`, `screen-laporan.webp`.

### Section 06 — Cara kerja

**Judul:** Mulai Kelola Laundry dalam Beberapa Langkah

1. **Daftar akun:** Buat akun dan daftarkan usaha laundry.
2. **Atur usaha:** Lengkapi profil, layanan, tarif, serta akun staf.
3. **Mulai menerima order:** Kasir mencatat pesanan dan pembayaran.
4. **Pantau sampai selesai:** Operator memperbarui proses, pemilik memantau laporan, pelanggan dapat mengecek status.

**CTA:** `Daftarkan Laundry Saya` → `/register`.

### Section 07 — Manfaat berdasarkan peran

**Judul:** Satu Sistem untuk Setiap Peran di Laundry

| Role | Manfaat yang dijanjikan |
|---|---|
| Pemilik / Admin Laundry | Memantau operasional, mengelola layanan dan staf, melihat laporan |
| Kasir | Melayani pelanggan, membuat order, mencatat pembayaran dan pengambilan |
| Operator | Mengelola progres cuci dan lokasi rak |
| Pelanggan | Melacak status order tanpa harus masuk ke dashboard staf |

**Penting:** Admin Laundry berbeda dengan Super Admin Platform. Jangan menjelaskan fungsi super admin di halaman penjualan konsumen.

### Section 08 — Harga berlangganan

**Judul:** Harga Jelas, Sesuaikan dengan Kebutuhan Usaha

**Rekomendasi MVP: satu paket aktif agar penawaran sederhana.**

**Paket Business (harga usulan, belum final):**
- Bulanan: **Rp99.000/bulan**.
- Tahunan: **Rp990.000/tahun** (ekuivalen Rp82.500/bulan, hemat Rp198.000 dibanding 12 bulan harga bulanan).
- Akses dashboard manajemen laundry.
- Fitur operasional yang memang tersedia dalam build production.
- 1 usaha / 1 outlet pada MVP.
- Batas staf dan transaksi **baru dicantumkan setelah ditegakkan dalam sistem**.
- Bantuan dasar sesuai kebijakan support yang tertulis.

**Toggle:** Bulanan | Tahunan; pergantian memperbarui harga, masa berlaku, dan keterangan penghematan. CTA `Pilih Paket` → alur checkout jika tersedia, atau `/register` dengan pilihan paket yang dipertahankan di backend.

Jangan menampilkan paket Pro multi-outlet atau paket Starter dengan limit teknis yang belum diterapkan.

**Kebijakan trial:** Tampilkan `Coba Gratis` dan durasinya hanya jika trial sudah benar-benar berfungsi. Jika belum, gunakan `Daftar Sekarang`.

### Section 09 — Kepercayaan & jaminan transparansi

**Judul:** Dibuat untuk Membantu Operasional Laundry Sehari-hari

Konten yang boleh digunakan sejak peluncuran:
- Screenshot aplikasi nyata.
- Video demo dari data fiktif.
- Penjelasan siapa yang mengembangkan dan mengelola layanan.
- Informasi support dan jam layanan aktual.
- Penjelasan privasi, penyimpanan, backup, dan akses data yang sesuai praktik nyata.

Konten yang **tidak boleh diada-adakan**: jumlah pengguna, logo klien, angka penghematan, rating, review, sertifikasi, jaminan uptime, atau testimonial. Setelah mendapat izin dari pelanggan beta, tampilkan studi kasus dan testimoni terverifikasi.

### Section 10 — FAQ

1. **Apakah harus memasang aplikasi di komputer?** Tidak, akses melalui browser. Koneksi internet diperlukan kecuali fitur offline benar-benar tersedia.
2. **Apakah bisa diakses dari HP?** Bisa melalui browser yang didukung dan tampilan responsif setelah diuji.
3. **Apakah staf bisa memiliki akun masing-masing?** Ya, sesuai role dan batas paket yang berlaku.
4. **Apakah pelanggan bisa mengecek status cucian?** Ya, melalui tracking order yang sudah disediakan.
5. **Apakah mendukung pembayaran DP?** Ya, fitur pencatatan pembayaran DP dan pelunasan tersedia; ini berbeda dari pembayaran langganan SaaS.
6. **Bagaimana kalau masa langganan habis?** Jelaskan aturan grace period, akses read-only, ekspor, dan retensi berdasarkan implementasi serta kebijakan sebenarnya.
7. **Bagaimana cara berlangganan?** Daftar, pilih paket, dan selesaikan pembayaran pada kanal resmi yang tersedia.
8. **Apakah ada biaya tersembunyi?** Cantumkan biaya tambahan atau pengecualian yang benar-benar berlaku, termasuk apakah harga termasuk pajak.

### Section 11 — CTA akhir

**Judul:** Siap Membuat Operasional Laundry Lebih Tertata?

**Deskripsi:** Mulai mencatat pesanan, mengelola proses cucian, dan memantau bisnis dari satu tempat.

**CTA primer:** `Mulai Sekarang` → `/register`  
**CTA sekunder:** `Tanya Tim Kami` → nomor WhatsApp resmi yang nanti ditentukan, bukan placeholder aktif.

### Section 12 — Footer

Kolom:
- **Brand:** logo, tagline, deskripsi singkat.
- **Produk:** fitur, cara kerja, harga.
- **Bantuan:** FAQ, kontak, email support.
- **Legal:** kebijakan privasi, syarat layanan.

Baris bawah: `© [TAHUN] [NAMA_BRAND]. Hak cipta dilindungi.` Gunakan nama legal perusahaan hanya jika benar-benar dimiliki/terdaftar.

## 6. Halaman company profile pendukung

### 6.1 Tentang Kami — `/tentang-kami`

Struktur: hero singkat, cerita mengapa sistem dibuat, masalah yang hendak diselesaikan, visi, prinsip produk, siapa pembuat dan pengelola, CTA. Jangan mengklaim memiliki PT, tim besar, pengalaman bertahun-tahun, atau ribuan pelanggan tanpa bukti.

**Copy awal:**
> Kami mengembangkan solusi digital untuk membantu pelaku usaha laundry mengelola operasional secara lebih tertata. Fokus kami adalah membuat pencatatan pesanan, proses cucian, pembayaran, dan pelaporan lebih mudah digunakan dalam aktivitas sehari-hari.

### 6.2 Kontak — `/kontak`

Tampilkan email bisnis, WhatsApp resmi, jam support, serta form dengan `nama`, `email/WhatsApp`, `nama usaha` (opsional), `pesan`. Gunakan validasi, rate limit, anti-spam, kebijakan penggunaan data, dan feedback sukses/gagal. Jangan menampilkan alamat kantor fiktif.

### 6.3 Kebijakan Privasi — `/kebijakan-privasi`

Bahas: data akun dan bisnis yang dikumpulkan, tujuan pemrosesan, dasar/persetujuan yang relevan, penyimpanan dan pengamanan, pihak pemroses pembayaran/hosting, retensi, hak subjek data, kontak privasi, dan perubahan kebijakan. Sesuaikan dengan hukum Indonesia dan praktik aktual; perlu peninjauan sebelum dipublikasikan.

### 6.4 Syarat Layanan — `/syarat-layanan`

Bahas: hak akses SaaS (bukan source code), paket dan penagihan, pembaruan harga, pembatalan, refund, masa tenggang, kepemilikan data pelanggan, ekspor data, dukungan, batas penggunaan, gangguan layanan, dan penghentian akun. Perlu peninjauan legal.

## 7. Panduan visual / UI

**Arah desain:** modern, bersih, profesional, ramah UMKM; gunakan banyak ruang putih, kartu sederhana, ilustrasi seperlunya, dan dashboard nyata.

**Palet usulan:**
- Primary navy: `#142B49` (kepercayaan / teks gelap).
- Blue: `#2563EB` (CTA dan tautan).
- Light background: `#F8FAFC`.
- White: `#FFFFFF`.
- Text: `#0F172A`.
- Muted: `#64748B`.
- Success: `#16A34A` untuk status yang benar-benar bermakna.

**Tipografi:** Inter atau Plus Jakarta Sans dengan fallback sans-serif, yang lisensinya sesuai penggunaan.  
**Komponen:** navbar sticky, tombol primer/sekunder, fitur cards, screenshot showcase, pricing toggle, FAQ accordion, form kontak, footer.  
**Mobile first:** CTA terlihat, judul tidak terlalu panjang, kartu pricing menumpuk vertikal, screenshot dapat digeser, area sentuh memadai.

**Aksesibilitas:** kontras cukup, focus state jelas, semantik heading H1–H3, label form, alt text, keyboard navigation, reduced motion.

**Animasi:** ringan dan tidak wajib; hindari animasi berat yang memperlambat ponsel.

## 8. UX dan konversi

**Primary conversion:** registrasi akun / usaha yang valid.  
**Secondary conversion:** klik kontak WhatsApp atau formulir konsultasi.

Alur:

```text
Pengunjung → Landing Page → Lihat Fitur / Harga → CTA
    ├── Belum login → Registrasi → Buat profil usaha → Dashboard / Pilih Paket
    ├── Sudah login → Dashboard
    └── Butuh bantuan → Kontak / WhatsApp resmi
```

CTA tidak boleh mengarahkan pelanggan ke checkout yang belum ada. Jika langganan belum otomatis, jelaskan langkah aktivasi dengan jujur.

**Event analytics yang dianjurkan:** `landing_view`, `hero_register_click`, `pricing_view`, `pricing_plan_select`, `faq_open`, `contact_click`, `signup_started`, `signup_completed`. Jangan mengirim data pribadi sensitif ke analytics; hormati preferensi consent yang berlaku.

**KPI:** visitor-to-signup rate, signup-to-activated-tenant rate, trial-to-paid conversion jika ada trial, churn setelah berbayar, dan jumlah permintaan demo.

## 9. Rekomendasi implementasi Laravel

Tidak perlu frontend baru untuk MVP jika Blade + TailwindCSS yang sekarang sudah tersedia.

### 9.1 Struktur kode yang disarankan

```text
resources/views/marketing/
├── layouts/app.blade.php
├── home.blade.php
├── about.blade.php
├── contact.blade.php
├── privacy.blade.php
├── terms.blade.php
└── components/
    ├── navbar.blade.php
    ├── hero.blade.php
    ├── features.blade.php
    ├── screenshots.blade.php
    ├── workflow.blade.php
    ├── pricing.blade.php
    ├── faq.blade.php
    ├── final-cta.blade.php
    └── footer.blade.php

public/images/marketing/
├── hero-dashboard.webp
├── screen-kasir.webp
├── screen-operator.webp
├── screen-tracking.webp
├── screen-pembayaran.webp
└── screen-laporan.webp
```

Nama file di atas adalah rancangan, bukan klaim mengenai struktur repository sekarang.

### 9.2 Contoh route (pseudocode yang harus disesuaikan)

```php
Route::view('/', 'marketing.home')->name('marketing.home');
Route::view('/tentang-kami', 'marketing.about')->name('marketing.about');
Route::view('/kebijakan-privasi', 'marketing.privacy')->name('marketing.privacy');
Route::view('/syarat-layanan', 'marketing.terms')->name('marketing.terms');
// GET /kontak untuk form; POST /kontak harus memakai controller,
// validasi request, rate limiting, proteksi spam dan CSRF.
// JANGAN menimpa route login, register, tracking, atau dashboard existing.
```

Karena `routes/web.php` belum diaudit pada versi terbaru, cek dahulu apakah `/` sudah digunakan untuk redirect/dashboard. Pastikan root path publik, sedangkan pengguna yang sudah login punya jalur mudah menuju dashboard.

### 9.3 SEO teknis

- Title: `Aplikasi Manajemen Laundry Online | [NAMA_BRAND]`.
- Meta description: `Kelola pesanan, pelanggan, proses cucian, pembayaran, dan laporan usaha laundry melalui satu aplikasi berbasis web. Kenali fitur [NAMA_BRAND].`
- Satu H1 per halaman; heading terurut.
- Open Graph / Twitter card menggunakan screenshot/brand resmi.
- Canonical URL, robots.txt, sitemap.xml.
- Favicon dan logo milik brand sendiri.
- Alt text pada screenshot, optimasi gambar WebP/AVIF, lazy loading di bawah hero.
- Structured data `SoftwareApplication` hanya dengan atribut akurat; jangan mengarang rating/review.

### 9.4 Keamanan dan keandalan

- Halaman pemasaran publik **tidak boleh** mengubah tenancy context akun secara tidak sah.
- Form kontak: CSRF, validasi, rate limit, sanitasi output, proteksi spam.
- Jangan memperlihatkan data pelanggan asli pada screenshot atau demo.
- Jangan menaruh credential atau API key di JavaScript frontend.
- Pastikan CTA register/login menggunakan rute resmi dan HTTPS.
- Pisahkan data operasional laundry dari data analitik landing page sesuai kebijakan privasi.

## 10. Aset yang harus disiapkan

| Aset | Minimum untuk rilis | Sumber |
|---|---|---|
| Nama brand dan logo | Wajib | Brand sendiri |
| Screenshot dashboard | Wajib | Aplikasi dummy |
| Screenshot kasir, operator, tracking | Wajib | Aplikasi dummy |
| Domain resmi dan SSL | Wajib | Domain bisnis |
| Email kontak | Wajib | Email bisnis |
| WhatsApp CS | Jika CTA WhatsApp ditampilkan | Nomor bisnis yang benar |
| Deskripsi paket dan kebijakan bayar | Wajib sebelum penagihan | Implementasi billing aktual |
| Kebijakan Privasi dan Syarat Layanan | Wajib sebelum publik | Draft yang ditinjau |
| Video demo singkat | Disarankan | Screen record produk |
| Testimoni | Opsional, setelah ada pelanggan | Izin dan bukti nyata |

## 11. Rencana pengerjaan

**Sprint A — Fondasi (MVP marketing)**
1. Tentukan brand, domain, dan pesan utama.
2. Bangun navbar, hero, masalah/solusi, fitur, cara kerja, CTA, footer.
3. Sambungkan login dan register aktual.
4. Pasang screenshot asli dan optimasi responsive.

**Sprint B — Konversi & trust**
1. Implementasi section pricing sesuai paket yang benar-benar tersedia.
2. FAQ, kontak, Tentang Kami, privasi, dan syarat layanan.
3. Analitik berbasis consent dan pengukuran CTA.
4. QA seluruh link, form, dan alur daftar.

**Sprint C — Persiapan launch**
1. Audit copy agar tidak ada klaim fitur fiktif.
2. Lighthouse / Core Web Vitals dan aksesibilitas.
3. Uji website mobile, desktop, serta koneksi internet lambat.
4. Soft launch, pantau registrasi, dan revisi berdasarkan feedback.

## 12. Acceptance criteria (Definition of Done)

- [ ] Landing page memiliki H1 jelas, manfaat yang sesuai fitur asli, dan CTA registrasi yang berfungsi.
- [ ] Pengunjung dapat login dan register tanpa route konflik.
- [ ] Pengguna login dapat menuju dashboard yang tepat.
- [ ] Seluruh screenshot berasal dari aplikasi dengan data dummy.
- [ ] Pricing dan trial hanya menampilkan ketentuan yang sudah diimplementasikan.
- [ ] Mobile dan desktop responsive, navigasi keyboard berfungsi.
- [ ] Halaman Tentang, Kontak, Kebijakan Privasi, dan Syarat Layanan dapat dibuka.
- [ ] Form kontak aman dan mempunyai pesan sukses/gagal.
- [ ] Seluruh tombol diuji, tidak ada link mati.
- [ ] Metadata SEO, favicon, sitemap, dan canonical terpasang.
- [ ] Tidak ada testimoni, angka bisnis, ataupun integrasi palsu.
- [ ] HTTPS, error monitoring, backup, dan kebijakan data SaaS disiapkan di sisi operasional.

## 13. Informasi bisnis yang masih harus diputuskan

1. Nama brand final dan ketersediaan domain.
2. Paket/harga final, masa trial, dan metode pembayaran yang sudah aktif.
3. Nomor WhatsApp dan email resmi untuk support.
4. Batas jumlah staf, outlet, dan transaksi untuk paket MVP.
5. Kebijakan setelah masa langganan habis, refund, backup, dan ekspor data.
6. Screenshot dashboard production/demo tanpa data asli.

**Keputusan inti:** Untuk versi pertama, utamakan landing page yang fokus pada **satu produk, satu paket utama, dan satu CTA utama: daftar akun**. Luaskan website setelah data penggunaan dan umpan balik pemilik laundry tersedia.
