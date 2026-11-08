# LAUNDIQO — SPESIFIKASI IMPLEMENTASI LANDING PAGE SAAS

**Jenis dokumen:** Product brief + UI/UX specification + implementation instructions untuk AI coding agent  
**Status:** Siap digunakan sebagai pedoman implementasi (bukan bukti fitur sudah terpasang)  
**Produk:** Laundiqo — platform SaaS manajemen usaha laundry  
**Tagline:** *Kelola Laundry, Lebih Mudah.*  
**Stack target:** Laravel, Blade, TailwindCSS, Vite, Alpine.js (sesuaikan dengan versi dan dependensi proyek aktual)  
**Bahasa antarmuka:** Bahasa Indonesia  
**Target pengguna:** Pemilik/pengelola usaha laundry skala mikro–menengah di Indonesia  
**Tujuan utama halaman:** Mengubah pengunjung menjadi pengguna yang menyelesaikan registrasi usaha.

> **PENTING UNTUK AI AGENT:** Dokumen ini adalah spesifikasi desain dan implementasi **website publik/marketing**. Aplikasi operasional, autentikasi, registrasi tenant, role, dashboard, transaksi laundry, dan subscription telah memiliki implementasi yang harus **dipertahankan**. Lakukan inspeksi repository dahulu. Jangan menebak nama route, model, field, harga, durasi trial, atau fitur yang belum tersedia. Jangan membangun ulang modul SaaS atau mengubah business logic hanya demi landing page.

---

## 0. PERINTAH UTAMA UNTUK AI CODING AGENT

Anda bertindak sebagai **Senior Laravel Engineer + Frontend/UI Engineer + Product Designer**. Tugas Anda adalah membuat landing page Laundiqo yang:

1. Modern, premium, minimalis, terlihat seperti produk B2B SaaS profesional.
2. Fokus pada conversion: CTA utama selalu menuju **alur registrasi yang sudah ada**, CTA sekunder membantu memahami produk.
3. Menggunakan identitas brand Laundiqo (navy, biru, cyan/turquoise, logo mesin cuci yang disediakan).
4. Memiliki animasi halus, bermakna, responsif, hemat resource, dan ramah aksesibilitas.
5. Berfungsi pada desktop, tablet, dan ponsel; tidak ada horizontal overflow.
6. Tidak mengganggu auth, tenant isolation, akses dashboard, layanan pembayaran, dan route yang sudah berjalan.
7. Menggunakan screenshot **nyata, disanitasi** dari sistem apabila tersedia; gunakan placeholder bergaya UI yang jelas bertanda ilustrasi bila belum tersedia.
8. Tidak menggunakan testimonial, angka pengguna, logo klien, statistik hasil, atau klaim keamanan yang tidak terverifikasi.
9. Mendukung pengelolaan konten/harga tanpa duplikasi berbahaya; manfaatkan sumber data plan yang sudah tersedia bila aman dan relevan.
10. Menghasilkan implementasi yang dapat dibangun, diuji, dan diserahkan dengan daftar file yang berubah.

### Aturan kerja WAJIB

- **Baca kode existing terlebih dahulu**: `composer.json`, `package.json`, `vite.config.*`, `routes/web.php`, `resources/views/layouts/*`, struktur komponen Blade, halaman auth/register, dan (bila ada) model/controller plan.
- Verifikasi branch aktif: utamakan `feature/saas` atau branch marketing turunan yang memang disepakati. **Jangan mengubah `main` secara tidak sengaja.**
- Gunakan pola proyek yang tersedia. Jangan memaksakan struktur baru apabila proyek sudah punya folder/layout yang serupa.
- Hindari menambahkan banyak dependency. Untuk versi pertama prioritaskan **Tailwind + Alpine.js + CSS + IntersectionObserver**. GSAP hanya jika benar-benar diperlukan dan ada alasan yang jelas.
- **Jangan menghapus, merombak, atau menimpa** route `/register`, `/login`, `/dashboard`, middleware auth/tenant/subscription, migrations, model transaksi, atau dashboard bisnis.
- Jika `/` sudah memiliki logic penting, telaah dan integrasikan landing page secara aman. Jangan menimpa redirect login/dashboard secara membabi buta.
- Jangan menyimpan API key, password demo, data pelanggan nyata, token, atau `.env` dalam view atau aset frontend.
- Jangan membuat tombol dekoratif yang tidak berfungsi. CTA harus punya tujuan yang benar, dan menu anchor harus mengarah ke section yang ada.
- Jika detail yang dibutuhkan belum ada (misalnya paket dan harga), **gunakan copy netral** atau komponen yang dapat disembunyikan; jangan mengarang seolah sudah aktif.
- Jaga perubahan dalam ruang lingkup marketing website. Dokumentasikan blocker yang benar-benar tidak bisa diselesaikan dari repository.

### Urutan pengerjaan agent

**Audit ringan → implementasi layout + hero → section produk → interaksi/animasi → integrasi CTA → SEO/aksesibilitas → build/test → laporan hasil.**

---

## 1. POSITIONING, TONE, DAN PESAN PEMASARAN

### 1.1 Positioning

**Laundiqo adalah aplikasi manajemen laundry online yang membantu pemilik usaha mencatat pesanan, memantau proses cucian, mengelola pembayaran, dan membaca laporan dalam satu tempat.**

**Bukan:** layanan pencucian pakaian, marketplace jasa laundry, penjualan source code, jasa pembuatan tugas kuliah, atau aplikasi konsumen untuk memesan kurir.

### 1.2 Audiens

- Pemilik laundry kiloan dan satuan.
- Pengelola laundry dengan kasir dan operator.
- Usaha laundry yang pencatatan order/prosesnya masih manual.
- Pemilik usaha yang membutuhkan tracking pesanan dan pembukuan operasional lebih rapi.

### 1.3 Tone copywriting

- Ramah, jelas, praktis, percaya diri tetapi **tidak hiperbolis**.
- Gunakan bahasa pemilik usaha: **pesanan, cucian, kasir, pelanggan, proses, rak, pelunasan, laporan**.
- Hindari jargon pengembang seperti *multi-tenant, webhook, queue, Blade,* dan *API* di copy publik.
- Jangan klaim "otomatis" jika proses status masih diperbarui manual oleh staf.
- Jangan sebut "real-time" kecuali benar-benar terverifikasi. Gunakan "status terbaru yang dicatat tim".

### 1.4 Pesan utama

**Headline (utama):**

> Kelola Laundry Lebih Mudah. Pantau Bisnis dari Mana Saja.

**Subheadline:**

> Catat pesanan, pantau proses cucian, kelola pembayaran, dan lihat laporan usaha dalam satu aplikasi laundry online yang mudah digunakan.

**CTA utama:** `Mulai Gunakan Laundiqo` (atau `Mulai Gratis` hanya jika trial benar-benar aktif dan syaratnya jelas).  
**CTA sekunder:** `Lihat Cara Kerja`.

**Kalimat manfaat singkat:**

> Dari cucian diterima sampai siap diambil, semua proses lebih tertata.

---

## 2. DESIGN SYSTEM LAUNDIQO

### 2.1 Arah desain

**Style:** Clean premium B2B SaaS, modern, airy, friendly, professional.  
**Kesan:** Bersih seperti laundry + tepercaya seperti software bisnis.  
**Karakter:** Rounded modern cards, whitespace luas, highlight gradient selektif, screenshot aplikasi nyata.

### 2.2 Palet warna

| Token | Hex | Kegunaan |
|---|---|---|
| `brand.navy` | `#0B1F3A` | Judul, footer, heading gelap |
| `brand.blue` | `#087FB8` | Tombol utama, link, elemen brand |
| `brand.cyan` | `#08C6D8` | Gradient, highlight, ring animasi |
| `brand.deepBlue` | `#075C91` | Hover CTA, ikon alternatif |
| `surface.ice` | `#F5FAFF` | Background section lembut |
| `surface.white` | `#FFFFFF` | Card dan section utama |
| `text.primary` | `#10243C` | Body heading |
| `text.muted` | `#59708B` | Teks pendukung |
| `stroke.soft` | `#DBEAF3` | Border kartu |

Gunakan teks gelap di atas surface terang untuk keterbacaan. Jangan menggunakan cyan cerah untuk teks kecil di atas putih tanpa kontras memadai. Gradient utama opsional `linear-gradient(135deg, #087FB8 0%, #08C6D8 100%)`.

### 2.3 Typography

- Rekomendasi: **Plus Jakarta Sans** atau **Inter**, satu keluarga font konsisten.
- Fallback: `ui-sans-serif, system-ui, sans-serif`.
- Heading hero: sekitar 44–64px desktop, 32–40px mobile; `font-bold`, line-height rapat tetapi terbaca.
- Section heading: 30–42px desktop, 26–32px mobile.
- Body: 15–18px; pendukung 14–16px.
- Maksimum lebar paragraf utama ±60–72 karakter.

### 2.4 Layout

- Max container: `max-w-7xl`, padding horizontal `px-5 sm:px-6 lg:px-8`.
- Jarak section desktop sekitar `py-20` sampai `py-28`, mobile `py-14` sampai `py-18`.
- Radius kartu 16–24px; tombol 10–14px.
- Bayangan halus, hindari glow berlebihan.
- Gunakan ikon konsisten; bila library ikon sudah ada gunakan yang sama, jangan mencampur 3 library.

### 2.5 Logo dan aset

- Gunakan **logo Laundiqo resmi dari pengguna** (simbol mesin cuci/awan/busur turquoise + wordmark bila tersedia).
- File SVG ikon sebelumnya dapat dipakai sebagai referensi visual; **jangan menggambar logo baru tanpa kebutuhan**.
- Sediakan/rujuk: logo horizontal di navbar, simbol sederhana untuk favicon, varian putih bila footer navy.
- **Agent harus memeriksa apakah aset tersebut sudah tersedia di repository**. Jika tidak tersedia, beri TODO jelas dan gunakan wordmark teks sementara, bukan path palsu.
- Jangan hotlink PNG/logo dari website kompetitor.

---

## 3. INFORMATION ARCHITECTURE & NAVIGASI

### 3.1 Menu desktop

`Laundiqo [logo]  |  Fitur  |  Cara Kerja  |  Tampilan Aplikasi  |  Harga  |  FAQ  |  Masuk  |  [Mulai Sekarang]`

Aturan:

- Navbar sticky dengan putih semi-transparan / border lembut dan `backdrop-blur` saat scroll.
- Logo klik menuju halaman utama.
- Menu section menggunakan anchor `#fitur`, `#cara-kerja`, `#tampilan`, `#harga`, `#faq`.
- Tombol `Masuk` menuju login yang sudah ada.
- CTA `Mulai Sekarang` menuju registrasi yang sudah ada.
- Bila login terdeteksi, prioritaskan CTA `Buka Dashboard` sesuai hak dan route existing; jangan tampilkan link yang salah.
- Nav aktif dapat diberi underline halus, tidak perlu kompleks.

### 3.2 Menu mobile

- Logo kiri, tombol menu kanan.
- Drawer/dropdown aksesibel dengan tombol yang memiliki `aria-expanded`, `aria-controls`.
- Klik anchor menutup menu dan scroll ke section.
- Jaga agar CTA register tetap jelas tanpa memakan seluruh layar.
- **Tidak boleh ada horizontal scroll.**

### 3.3 Struktur halaman

1. Sticky navbar.
2. Hero animasi + dashboard preview.
3. Problem → Solution.
4. Bento Grid fitur unggulan.
5. Interactive Workflow (alur laundry).
6. Product Showcase dengan tab per peran.
7. How It Works (mulai dalam 3 langkah).
8. Pricing bulanan/tahunan (**dari paket aktual**).
9. FAQ accordion.
10. Final CTA.
11. Footer.

**Optional setelah ada bukti nyata:** testimonial, mitra usaha, metrik pengguna. **Jangan ditambahkan sebagai data palsu.**

---

## 4. SPESIFIKASI DETAIL SETIAP SECTION

### 4.1 SECTION HERO — `#beranda`

**Fungsi:** Komunikasikan nilai bisnis dalam <5 detik dan arahkan ke registrasi.

**Layout desktop:** dua kolom atau headline terpusat di atas mockup dashboard lebar. Pilih salah satu yang paling rapi, konsisten pada seluruh halaman.  
**Mobile:** headline dahulu, CTA, mockup dengan rasio aman/tidak terpotong.

**Badge kecil (opsional):** `Aplikasi Manajemen Laundry Online`.

**Headline:**

> Kelola Laundry Lebih Mudah. Pantau Bisnis dari Mana Saja.

**Subheadline:**

> Catat pesanan, pantau proses cucian, kelola pembayaran, dan lihat laporan usaha dalam satu aplikasi yang lebih tertata.

**CTA:**

- Primary: `Mulai Gunakan Laundiqo` → route registrasi existing.
- Secondary: `Lihat Cara Kerja` → `#cara-kerja`.

**Visual hero:**

- Screenshot dashboard **asli** dari data dummy, dalam frame browser/laptop modern.
- 1–2 floating UI cards **berlabel ilustrasi** (mis. `Order diproses`, `Siap diambil`) jika memakai data contoh.
- Ornamen blur radial biru/cyan di background dengan opacity rendah.
- Hindari menampilkan "Rp X omzet" sebagai klaim nyata bila mockup tidak jelas bertanda contoh.

**Animasi hero:**

- Headline dan CTA fade-up berurutan (`0`, `100`, `200ms` delay).
- Frame dashboard fade/slide-up (`500–650ms`).
- Floating card bobbing perlahan (`translateY ±6px`, 5–7 detik, ease-in-out), berhenti bila reduced motion.
- Jangan memakai scroll hijacking atau autoplay berisik.

**Acceptance criteria:** CTA register/login benar, hero responsive, mockup tidak terpotong, animasi dimatikan pada reduced motion.

### 4.2 SECTION MASALAH DAN SOLUSI — `#solusi`

**Section title:** `Masih Mengelola Laundry dengan Cara Manual?`

**Deskripsi:** `Pesanan yang menumpuk, status cucian yang sulit dipantau, dan laporan yang tersebar membuat operasional tidak efisien.`

Tampilkan 3 pasang masalah → solusi:

| Masalah | Dengan Laundiqo |
|---|---|
| Order dicatat di buku atau chat | Data order tersimpan lebih rapi dalam sistem |
| Staf sulit mengetahui posisi cucian | Status proses dan rak bisa diperiksa |
| Pembayaran dan rekap terpisah | Riwayat pembayaran serta laporan tersedia |

**Visual:** icon cards / perbandingan dua kolom, bukan foto stok acak.

**Animasi:** scroll reveal dengan stagger ringan. Jangan animasikan seluruh paragraf per huruf.

### 4.3 SECTION FITUR — `#fitur`

**Section title:** `Semua yang Dibutuhkan untuk Mengelola Laundry`  
**Subheading:** `Satu tempat untuk kasir, operator, dan pemilik usaha.`

**Bento grid 6 kartu** (responsif 1 kolom mobile, 2 tablet, 3 desktop):

1. **Manajemen Pesanan** — Catat order dan layanan dengan lebih praktis.
2. **Tracking Cucian** — Pelanggan dapat mengecek status pesanan melalui nomor order (gunakan penjelasan privasi sesuai fitur aktual).
3. **Alur Produksi** — Pantau tahap diterima, dicuci, dikeringkan, disetrika, hingga siap diambil.
4. **Pembayaran Fleksibel** — Catat pembayaran penuh, DP, dan pelunasan sesuai fitur yang aktif.
5. **Manajemen Rak** — Ketahui tempat penyimpanan cucian yang telah diproses.
6. **Laporan Usaha** — Lihat rekap order serta laporan PDF/Excel jika benar tersedia dalam release.

**Visual:** tiap card punya icon, title, desc maksimal 2–3 baris, plus contoh mini-UI yang tetap ringan jika tersedia.

**Animasi:** fade-up bertahap pada saat visible, hover naik **maksimal 4px** dengan shadow lembut (tidak mengganggu touch).

### 4.4 SECTION WORKFLOW INTERAKTIF — `#cara-kerja`

**Title:** `Dari Cucian Diterima Sampai Selesai, Semua Lebih Terpantau.`

Tahapan (gunakan label status yang cocok dengan business logic existing):

1. **Diterima** — Kasir membuat pesanan.
2. **Dicuci** — Operator memperbarui proses pencucian.
3. **Dikeringkan** — Cucian menuju tahap pengeringan.
4. **Disetrika** — Pakaian dirapikan.
5. **Siap Diambil** — Barang diletakkan di rak dan siap diserahkan.
6. **Selesai** — Kasir menyelesaikan penyerahan dan kewajiban pembayaran sesuai alur existing.

**Interaksi wajib:**

- Stepper/tab yang dapat diklik dan diakses melalui keyboard.
- Panel berisi ikon, judul, penjelasan, dan progress indicator.
- Tombol `Sebelumnya` / `Selanjutnya` disable di batas awal/akhir.
- Animasi transisi panel 200–300ms; **tidak otomatis berpindah** (menghindari distraksi).
- Di mobile gunakan tombol step kecil yang dapat dibungkus atau scroll horizontal pada *kontainer stepper saja* dengan indikasi yang jelas.
- Gunakan Alpine state (`activeStep`) atau vanilla JS sesuai kebiasaan repository; jangan mengubah data order sesungguhnya.

**Catatan:** Ini simulasi interaktif *edukasi produk*, bukan dashboard transaksi live.

### 4.5 SECTION DASHBOARD SHOWCASE — `#tampilan`

**Title:** `Kenali Laundiqo dari Setiap Peran`  
**Subheading:** `Tampilan yang sesuai dengan pekerjaan pemilik, kasir, dan operator.`

**Tab:** `Admin`, `Kasir`, `Operator`, `Tracking Pelanggan`.

| Tab | Konten / Screenshot yang ditampilkan |
|---|---|
| Admin | Dashboard ringkasan dan laporan |
| Kasir | Daftar order, pembuatan order, pembayaran |
| Operator | Daftar pekerjaan dan update status |
| Tracking | Form cek nomor order dan status pesanan |

**Aturan visual:**

- Gunakan screenshot asli dari sistem dengan **data demo** (nama/nomor telepon/alamat pelanggan fiktif).
- Screenshot perlu disesuaikan ukuran dan diberi `alt` deskriptif.
- Jika belum ada screenshot, pakai panel placeholder yang **jelas bertuliskan** `Preview [role] akan ditambahkan` dan CTA tidak mengklaim gambar itu hasil asli.
- `crossfade` ketika tab berubah, sekitar 200–300ms.
- Tab wajib accessible (`role=tablist`, `aria-selected`, keyboard atau tombol biasa yang tetap semantik).
- Jangan meng-embed dashboard sensitif di iframe publik.

### 4.6 SECTION MULAI PAKAI — `#mulai`

**Title:** `Mulai Kelola Laundry dalam 3 Langkah`.

1. **Daftarkan usaha** — Buat akun dan profil bisnis melalui registrasi yang sudah tersedia.
2. **Atur layanan dan staf** — Sesuaikan layanan serta pengguna sesuai izin akun.
3. **Mulai catat pesanan** — Kasir dan operator menggunakan dashboard Laundiqo.

**Visual:** tiga card / timeline sederhana; icon berbeda; CTA `Daftar Sekarang`.

**Jangan klaim aktivasi instan** jika real flow masih menunggu konfirmasi admin atau pembayaran.

### 4.7 SECTION HARGA — `#harga`

**Title:** `Pilih Paket yang Sesuai dengan Usaha Laundrymu`  
**Subheadline:** `Pilih langganan yang jelas manfaatnya, sesuai kebutuhan operasional.`

**Kebutuhan UI:**

- Toggle `Bulanan` / `Tahunan` (Alpine.js), tampilkan satuan periode dengan benar.
- Nilai dan fitur paket diambil dari **sumber plan existing** bila ada endpoint/controller yang aman untuk publik, atau konfigurasi marketing yang terpusat dan mudah diubah.
- **Jangan hardcode Rp49.000 / Rp99.000 / Rp199.000 sebagai harga aktif** tanpa memeriksa data subscription dan konfigurasi billing aktual.
- Jika hanya satu paket tersedia, tampilkan **satu kartu unggulan**, bukan memalsukan tiga tier.
- Jika nominal belum ditentukan, gunakan `Hubungi Kami` atau sembunyikan harga sementara; jangan tampilkan harga kosong/`Rp0` yang menyesatkan.
- Jika free trial aktif, tampilkan durasi persis dari konfigurasi; jika tidak aktif, jangan tampilkan `Gratis 14 Hari`.
- Pastikan CTA pada kartu harga mengikuti flow checkout/registrasi aktual; jangan mengirim ID plan yang tidak ada.

**Contoh struktur card (tanpa angka fiktif):**

- Nama paket.
- Harga dan periode.
- Ringkasan batas pengguna/outlet hanya jika nyata.
- 4–6 fitur utama yang benar-benar termasuk.
- CTA `Pilih Paket` / `Mulai Sekarang`.
- Keterangan penagihan tahunan dan syarat penggunaan bila ada.

**Animasi:** hanya transisi teks/harga 150–250ms, tidak perlu angka count-up berulang.

### 4.8 SECTION FAQ — `#faq`

**Title:** `Pertanyaan yang Sering Ditanyakan`.

Pertanyaan yang disarankan (jawaban harus sesuai implementasi/policy aktual):

1. **Apa itu Laundiqo?**  
   `Laundiqo adalah aplikasi online untuk mengelola operasional usaha laundry, mulai dari pencatatan order hingga laporan.`
2. **Apakah saya perlu menginstal aplikasi?**  
   `Laundiqo dapat diakses melalui browser pada perangkat yang terhubung internet.`
3. **Apakah bisa digunakan oleh kasir dan operator?**  
   `Ya, Laundiqo memiliki pembagian akses berdasarkan peran pengguna, dengan ketentuan sesuai paket.`
4. **Apakah pelanggan bisa mengecek status cucian?**  
   `Tersedia halaman tracking menggunakan nomor order dengan informasi yang dibatasi sesuai pengaturan sistem.`
5. **Bagaimana cara pembayaran langganan?**  
   Jawab sesuai metode pembayaran yang **benar-benar aktif**.
6. **Apa yang terjadi jika langganan habis?**  
   Jawab sesuai kebijakan akses dan retensi data yang **benar-benar diterapkan**.
7. **Bagaimana jika butuh bantuan?**  
   Tampilkan kanal support valid (WhatsApp/email) yang benar-benar tersedia.

**Interaksi:** accordion single-open atau multi-open, bisa keyboard, indikator `+ / −` berubah, animasi height ringan tanpa layout jump ekstrem.

### 4.9 FINAL CTA

**Headline:** `Siap Membuat Operasional Laundry Lebih Tertata?`  
**Deskripsi:** `Mulai gunakan Laundiqo untuk mengelola pesanan, proses cucian, dan laporan usaha dalam satu tempat.`  
**Primary CTA:** `Mulai Sekarang` → registrasi aktual.  
**Secondary CTA:** `Lihat Fitur` → `#fitur`.

**Visual:** panel gradient navy-blue-cyan yang elegan, copy putih dengan kontras cukup, tidak memerlukan gambar besar.

### 4.10 FOOTER

Kolom:

- **Brand:** Logo + `Laundiqo — Kelola Laundry, Lebih Mudah.`
- **Produk:** Fitur, Cara Kerja, Harga, FAQ.
- **Akun:** Daftar, Masuk.
- **Informasi:** Tentang Kami, Kontak, Kebijakan Privasi, Syarat Layanan **hanya jika halaman tersebut sudah ada atau dibangun dengan konten sah**.

`© {{ date('Y') }} Laundiqo. All rights reserved.`

Jangan mengarang alamat kantor, nomor WhatsApp, email support, badan hukum, atau tautan sosial. Ambil dari konfigurasi yang nyata; jika belum tersedia, jangan tampilkan.

---

## 5. ANIMASI DAN INTERAKSI (SPESIFIKASI TEKNIS)

### 5.1 Prinsip animasi

**Setiap animasi harus meningkatkan pemahaman atau hierarchy.** Kurangi gerakan dekoratif yang menyulitkan pemilik usaha membaca informasi.

| Elemen | Efek | Durasi | Catatan |
|---|---|---|---|
| Hero headline | Fade + translateY 16px | 500ms | Hanya saat awal tampil |
| Hero dashboard | Fade + translateY 24px | 600ms | Setelah headline |
| Floating status cards | translateY ±6px | 5–7s loop | Hentikan jika reduced motion |
| Feature cards | Reveal stagger | 400–550ms | Sekali saat masuk viewport |
| Hover feature card | translateY -4px | 180–220ms | Desktop; tetap fokus keyboard |
| Workflow panel | Crossfade / subtle slide | 200–300ms | Berdasarkan klik pengguna |
| Product showcase | Crossfade | 200–300ms | Tanpa autoplay |
| Pricing toggle | Fade angka/harga | 150–250ms | Hindari layout shift |
| FAQ | Expand/collapse | 180–250ms | `aria-expanded` wajib |

### 5.2 Preferred reduced motion

- Hormati `@media (prefers-reduced-motion: reduce)`.
- Nonaktifkan float loop, reveal transform, scroll smooth jika user meminta reduced motion.
- Semua informasi harus tetap terlihat tanpa JavaScript / tanpa animasi sebisa mungkin.
- Jika JS gagal, jangan biarkan seluruh section punya `opacity: 0` permanen.

### 5.3 Strategi implementasi

- Gunakan **CSS transitions/keyframes** untuk floating dan hover.
- Gunakan **IntersectionObserver** untuk reveal; tambahkan kelas hanya setelah JS aktif (`.js` / `.motion-ready`) agar konten tidak tersembunyi jika JS gagal.
- Gunakan **Alpine.js** jika dependency sudah tersedia; bila belum, evaluasi penambahan minimal untuk FAQ/tabs/toggle atau gunakan vanilla JS.
- Hindari library animasi besar sebelum ada kebutuhan yang tidak bisa diselesaikan dengan CSS.
- Animasikan `transform` dan `opacity`; hindari `top`, `left`, `box-shadow` intensif, dan `filter: blur()` bergerak terus-menerus.
- Tidak perlu parallax kompleks, particle canvas, video background, cursor follower, infinite marquee, scroll hijacking, atau audio autoplay.

### 5.4 Contoh pola CSS aman (boleh disesuaikan)

```css
/* Konten tetap terlihat secara default tanpa JavaScript. */
.reveal { opacity: 1; transform: none; }

@media (prefers-reduced-motion: no-preference) {
  .js.motion-ready .reveal {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity .55s ease, transform .55s ease;
  }
  .js.motion-ready .reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
  }
  @keyframes gentle-float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
  }
  .hero-float { animation: gentle-float 6s ease-in-out infinite; }
}
```

Implementasi IntersectionObserver harus menampilkan konten jika API tidak tersedia / ketika terjadi error. Jangan mengasumsikan setiap browser mendukung plugin Alpine yang belum diinstal.

---

## 6. STRUKTUR FILE (REKOMENDASI, BUKAN HARUS DIPAKSA)

Agent harus memeriksa struktur proyek terlebih dahulu. Bila belum ada pola marketing component, struktur berikut boleh digunakan:

```text
resources/
├── views/
│   ├── layouts/
│   │   └── marketing.blade.php
│   └── landing/
│       ├── index.blade.php
│       └── partials/
│           ├── navbar.blade.php
│           ├── hero.blade.php
│           ├── problem-solution.blade.php
│           ├── features.blade.php
│           ├── workflow.blade.php
│           ├── showcase.blade.php
│           ├── how-it-works.blade.php
│           ├── pricing.blade.php
│           ├── faq.blade.php
│           ├── final-cta.blade.php
│           └── footer.blade.php
├── css/
│   └── landing.css
└── js/
    └── landing.js
public/
├── images/
│   └── landing/
│       └── (logo dan screenshot DEMO yang benar-benar tersedia)
└── favicon.svg (jika sudah disediakan)
```

**Catatan implementasi:**

- Prioritaskan `@include`, komponen Blade yang sudah ada, dan `@vite` sesuai konfigurasi proyek.
- Jangan merusak `resources/css/app.css` / `resources/js/app.js` existing. Tambahkan entry khusus jika dibutuhkan dan sinkronkan dengan Vite.
- Bila `layouts/guest.blade.php` dipakai untuk register/login, jangan ubah global layout yang merusak halaman tersebut.
- Pertahankan CSRF, middleware auth, dan security headers existing.
- Jika memakai asset bundling khusus, ikuti pola yang sudah ada.

---

## 7. ROUTING DAN INTEGRASI LARAVEL

### 7.1 Route publik

- Landing: `GET /` (jika aman dan tidak bentrok dengan route existing).
- `GET /login` dan `/register` menggunakan route existing — **jangan dibuat ulang**.
- Menu anchor tetap pada landing.
- Jika ada halaman privacy/terms/contact saat ini, gunakan route yang sudah tersedia.

### 7.2 Resolusi link auth

Di Blade, gunakan resolusi route yang sesuai dengan aplikasi aktual; `route('register')` dan `route('login')` hanya jika named route itu benar-benar ada.

Bila user sudah login:

- Tampilkan CTA menuju dashboard sesuai route/role existing.
- Jangan mengasumsikan semua user memiliki route dashboard yang sama.
- Jangan memaksa logout atau redirect membabi buta dari halaman landing.

### 7.3 Pricing data

- Inspeksi model `Plan` / `Subscription` / tabel atau konfigurasi yang sudah ada.
- Sediakan view data aman untuk umum (nama paket, nominal, periode, fitur yang memang boleh dipublikasikan).
- Jangan mengekspose API key gateway, invoice internal, tenant ID pengguna lain, atau aturan diskon rahasia.
- Jika tidak ada backend publik yang siap, boleh gunakan struktur data konfigurasi sementara **yang mencerminkan harga terkonfirmasi**, dan beri catatan sumber nilai.
- Hindari menambahkan migrasi database hanya demi landing page.

### 7.4 Fokus keamanan

- Tidak ada endpoint baru untuk membaca data laundry secara publik.
- Gambar screenshot harus menggunakan data dummy; hapus metadata sensitif.
- Jangan membuat public preview yang mengakses model `Order`, `Payment`, atau `Customer` tanpa kebutuhan dan kontrol tenant.
- Tracking publik yang sudah ada tidak perlu diubah karena landing page.

---

## 8. RESPONSIVE, ACCESSIBILITY, DAN UX

**Target minimum:** mobile 360–430px; tablet 768px; desktop 1280–1440px.

### Mobile-first

- Hero headline dan CTAs mudah dibaca; tombol touch target sekitar 44px.
- Bento grid 1 kolom di mobile.
- Mockup screenshot gunakan `object-contain`, wrapper aspect ratio terjaga, tidak overflow.
- Tabs bisa scroll horizontal pada area tabs dengan fokus yang terlihat.
- Pricing cards menjadi satu kolom; toggle periode tetap dapat dijangkau.
- Navbar mobile tetap menampilkan logo jelas.

### Aksesibilitas

- Heading hierarkis (`h1` hanya untuk hero; `h2` tiap section).
- `<button>` untuk interaksi, `<a href>` untuk navigasi, bukan `<div onclick>`.
- Gunakan `aria-label`, `aria-expanded`, `aria-controls` saat diperlukan.
- Fokus keyboard jelas dengan `focus-visible`.
- Kontras teks minimal setara WCAG AA untuk teks normal dan tombol penting.
- Alt screenshot menjelaskan fungsi; gambar dekoratif `alt=""`.
- Pastikan accordion/tab dapat digunakan dengan keyboard dan screen reader.
- Reduced motion wajib.

### Form dan conversion

- Landing tidak perlu membuat form registrasi baru; arahkan ke halaman register existing.
- Jika contact CTA digunakan, tautkan ke kanal yang sudah benar-benar dikonfigurasi.
- Jangan tampilkan popup agresif atau chat widget yang menutupi tombol utama di mobile.

---

## 9. SEO, SHARE PREVIEW, DAN PERFORMANCE

### SEO

- Title: `Laundiqo — Aplikasi Manajemen Laundry Online`.
- Meta description contoh: `Kelola pesanan, proses cucian, pembayaran, dan laporan usaha laundry lebih mudah bersama Laundiqo. Kenali fitur dan mulai gunakan aplikasinya.`
- Open Graph title, description, dan image bila aset OG tersedia.
- Canonical harus memakai domain asli aplikasi dari konfigurasi produksi, **jangan hardcode domain contoh**.
- Gunakan markup heading semantik dan nama section jelas.
- Structured data `SoftwareApplication` opsional, hanya jika fakta produk/penawaran benar.
- Privacy/terms harus benar sebelum publikasi, terutama karena sistem menyimpan data pelanggan dan transaksi usaha.

### Performance

- Gunakan SVG untuk logo/favicon dan WebP/AVIF untuk screenshot bila memungkinkan.
- `loading="lazy"` untuk gambar di bawah hero; **jangan lazy-load** aset hero yang menjadi LCP utama.
- Tetapkan ukuran/dimensi gambar untuk mengurangi CLS.
- Jangan memakai screenshot resolusi berlebihan untuk ukuran card kecil.
- Hindari 3D berat, video background, atau CDN library tanpa alasan.
- Target internal: halaman mudah digunakan pada ponsel kelas menengah dan koneksi seluler biasa.

### Observabilitas marketing (opsional)

Jika aplikasi sudah memiliki analytics yang sesuai kebijakan privasi, catat aksi sederhana:

- Click `Hero CTA Register`.
- Click `Pricing CTA`.
- Switch `Monthly/Yearly`.
- View `Product Showcase`.

Jangan memasang analytics baru yang mengumpulkan data pribadi tanpa kebijakan dan persetujuan yang diperlukan.

---

## 10. ACCEPTANCE TESTS / DEFINITION OF DONE

**Landing page baru baru dianggap selesai jika semua kriteria berikut dipenuhi.**

### A. Integrasi

- [ ] Landing dapat dibuka tanpa login melalui route publik yang tepat.
- [ ] Registrasi tenant existing tetap bekerja.
- [ ] Login dan redirect dashboard existing tetap bekerja untuk role terkait.
- [ ] Tidak ada konflik route, middleware, atau named route.
- [ ] Fitur operasional SaaS tidak berubah dan tidak memunculkan error.
- [ ] CTA yang terlihat memiliki destination valid.

### B. Visual dan responsif

- [ ] Identitas Laundiqo konsisten dengan logo dan warna brand.
- [ ] Hero dan semua 10 section utama tampil benar.
- [ ] Tidak ada horizontal overflow pada 360px, 390px, 768px, 1280px.
- [ ] Mockup/screenshot asli aman (data dummy) atau placeholder jelas.
- [ ] Navbar sticky dan mobile menu berfungsi.
- [ ] Footer tidak memuat informasi perusahaan rekaan.

### C. Interaksi dan animasi

- [ ] Scroll reveal tampil lembut; konten tetap terlihat bila JS gagal.
- [ ] Floating hero dinonaktifkan dengan reduced motion.
- [ ] Workflow bisa diklik, Next/Prev bekerja, boundary disabled benar.
- [ ] Tab showcase berfungsi dan perubahan konten jelas.
- [ ] Pricing toggle bekerja dengan data dari paket aktual.
- [ ] FAQ accordion berfungsi dengan keyboard.
- [ ] Tidak ada animasi berlebihan yang mengganggu navigasi.

### D. Teknis

- [ ] `npm run build` berhasil (atau build command ekuivalen pada repo).
- [ ] `php artisan route:list` tidak menunjukkan benturan yang tidak diinginkan.
- [ ] `php artisan test` berjalan; bila ada test gagal yang unrelated, jelaskan secara terpisah.
- [ ] Console browser tanpa error JS yang berasal dari perubahan landing.
- [ ] Tidak ada secret atau customer data dalam asset publik.
- [ ] CTA register/login diuji pada user guest dan user authenticated.
- [ ] Meta SEO dasar tersedia.

### E. Dokumentasi akhir agent

- [ ] Daftar file dibuat/diubah.
- [ ] Daftar dependency baru jika ada + alasannya.
- [ ] Perintah instalasi/build.
- [ ] Apa yang dapat diverifikasi dan apa yang masih membutuhkan pengujian manusia.
- [ ] Lokasi penggantian screenshot dan logo jika aset belum tersedia.
- [ ] Screenshot desktop/mobile hasil akhir jika lingkungan mendukung.

---

## 11. RENCANA EKSEKUSI BERTAHAP

### Fase 1 — Fondasi visual

1. Audit routes/layout/assets yang sudah ada.
2. Buat marketing layout dan navbar responsif.
3. Implementasikan hero dengan copywriting final, CTA benar, dan frame screenshot.
4. Implementasikan token warna, typography, spacing, dan tombol.
5. Validasi desktop/mobile.

### Fase 2 — Penjelasan produk

6. Problem/Solution section.
7. Bento Features.
8. Workflow interaktif.
9. Showcase 4 role dengan tab.
10. How It Works.

### Fase 3 — Conversion

11. Integrasikan pricing sesuai plan aktual.
12. FAQ interaktif dengan jawaban sesuai kebijakan asli.
13. Final CTA dan footer.
14. Koreksi CTA register/login berdasarkan route sebenarnya.

### Fase 4 — Polish + QA

15. Tambahkan reveal animasi, floating terbatas, microinteraction.
16. Reduced motion, keyboard accessibility, responsivitas.
17. Optimasi gambar, meta SEO, performa.
18. Build, test, route audit, cek browser console.
19. Dokumentasikan hasil serta TODO yang masih menunggu aset/data dari pemilik produk.

**Prioritas bila waktu terbatas:** Navbar + Hero + Showcase + Fitur + Pricing + CTA, kemudian workflow/animasi lanjutan.

---

## 12. PANDUAN KONTEN DAN HAL-HAL YANG DILARANG

### Klaim yang boleh jika fitur sudah diverifikasi

- Mengelola order dan pelanggan.
- Melacak status cucian dengan nomor order.
- Pembayaran DP dan pelunasan.
- Mengelola tahapan produksi dan rak.
- Menampilkan laporan serta ekspor bila tersedia.
- Mendukung akun admin/kasir/operator.

### Klaim yang tidak boleh tanpa bukti

- "Dipercaya 10.000+ laundry".
- "Tingkatkan omzet 300%".
- "100% aman dan bebas bug".
- "Berjalan otomatis real-time tanpa input".
- "Sudah digunakan oleh brand X".
- "Gratis selamanya" atau trial 14 hari jika belum dikonfigurasi.
- "Harga hanya Rp..." jika berbeda dengan billing aktif.

### Jangan membuat fitur palsu pada landing

- Tidak perlu notifikasi WhatsApp otomatis jika belum ada.
- Tidak perlu integrasi printer thermal jika belum tersedia.
- Tidak perlu multi-outlet jika fitur belum dibangun.
- Tidak perlu aplikasi Android/iOS native jika saat ini berbasis web.
- Jangan pakai logo pelanggan atau testimoni tanpa izin.

---

## 13. OUTPUT YANG HARUS DILAPORKAN AGENT

Setelah implementasi, balas dengan format berikut:

```markdown
# Implementasi Landing Page Laundiqo — Hasil

## Yang sudah dikerjakan
- ...

## File yang dibuat/diubah
- path/file — alasan

## Rute & integrasi
- Landing: ...
- Register: ...
- Login: ...
- Dashboard: ...

## Animasi/interaksi
- ...

## Validasi
- Build: LULUS/GAGAL + hasil
- Test: LULUS/GAGAL + hasil
- Responsivitas: diuji pada ...
- Aksesibilitas: ...

## Belum selesai / butuh input
- ...

## Cara menjalankan
1. ...
2. ...
```

Agent **tidak boleh menyatakan selesai** bila belum menjalankan build/test yang memungkinkan pada lingkungan tersedia. Jika tidak dapat menjalankan, tuliskan bahwa verifikasi belum dilakukan.

---

## 14. PROMPT SINGKAT (COPY-PASTE) UNTUK MEMULAI AI AGENT

> Baca dan ikuti seluruh dokumen `laundiqo-landing-page-ai-agent-spec.md`. Implementasikan landing page publik Laundiqo di repository Laravel SaaS yang sudah ada, dengan Blade + Tailwind, Alpine.js hanya bila diperlukan, animasi halus serta mobile-first. **Audit struktur route, auth, tenant, subscription, Vite dan aset yang ada sebelum mengubah kode.** Jangan merusak register/login/dashboard dan jangan membuat data, testimoni, harga, trial, atau fitur fiktif. Gunakan logo Laundiqo resmi yang tersedia dan screenshot dummy asli bila ada. Bangun tiap section sesuai spesifikasi, pastikan CTA aktif, lakukan build/test, lalu berikan ringkasan file yang berubah, hasil verifikasi, dan TODO.

---

## 15. KEPUTUSAN PRODUK YANG PERLU DICEK DARI REPOSITORY

Dokumen ini sengaja tidak mengarang detail tersebut. Agent harus menemukan konfigurasi real atau menandainya sebagai TODO:

| Item | Cara menentukan |
|---|---|
| Named route register/login/dashboard | Periksa `routes/web.php`, auth routes, middleware |
| Trial tersedia atau tidak | Periksa flow registration + subscription |
| Harga dan jumlah paket | Periksa model/tabel/config plan aktif |
| Logo dan favicon final | Cek `public/` dan aset yang diberikan pengguna |
| Screenshot dashboard | Ambil dari instance demo dengan data dummy |
| Kanal customer support | Cek konfigurasi resmi yang memang tersedia |
| URL privacy dan terms | Cek route/view legal, jangan buat klaim hukum palsu |
| Nama role dan label status | Periksa model, enum, permission dan workflow sistem |

**Hasil yang dituju:** Laundiqo memiliki website company profile/landing SaaS yang rapi, premium, interaktif, cepat, dan siap memandu pemilik usaha ke proses pendaftaran yang sudah berjalan—tanpa mengorbankan stabilitas aplikasi inti.
