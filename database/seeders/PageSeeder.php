<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('username', 'admin')->first();
        $authorId = $admin?->id;

        $tentangLawnuContent = <<<'HTML'
<p>Lembaga Wakaf dan Pertanahan Nahdlatul Ulama merupakan lembaga yang lahir di NU sejak masa Hadratus Syaikh KH. Hasyim Asy'ari. Sebuah dokumen autentik berupa STATUTEN dan REGLEMENT STIEHTING WAQFIAH telah dibuat pada tanggal 23 Februari 1937 di hadapan Notaris Hendrik Wiliem Nazembreg, Surabaya, yang terdiri atas 11 pasal atau artikel. Salah satu pasalnya menyebutkan bahwa Perhimpunan Nahdlatul Ulama' secara resmi mendirikan Dewan Pengurus Wakaf, sebagai Rois adalah Hadratus Syaikh KH. Hasyim Asy'ari, dan sebagai Khatib adalah KH. Wahab Hasbullah.</p>
<p>Keberadaan Stiehting Waifish Nahdlatul Ulama' tersebut, telah dilengkapi dengan anggaran rumah tangga yang terdiri atas 31 pasal, yang salah satu pasalnya menyebutkan bahwa "harta wakaf boleh diambil buah atau hasilnya untuk kepentingan umum."</p>
<p>Anggaran Rumah Tangga Stiehting Waqfiah Nahdlatul Ulama' tersebut disetujui secara resmi dalam Konggres Perhimpunan Nahdlatul Ulama' ke 14, tanggal 4-5 Juli 1939, di Magelang dan dijadikan Plant Nahdlatul Ulama' secara Nasional.</p>
<p>Struktur Stiehting Waqfiah Nahdlatul Ulama' dari Mu'tamar ke Mu'tamar yang lain mengalami reposisi struktur, namun nama tetap menggunakan Stiehting Waqfiah. Pada Mu'tamar Nahdlatul Ulama' ke 15, tanggal 10-15 Desember 1940, posisi Stiehting Waqfiah berada di bawah bagian harta yang langsung dalam pembinaan dan pengawasan Syuriyah.</p>
<p>Perkembangan selanjutnya nama Stiehting Waqfiah menjadi sub unit tersendiri yang berkedudukan di Surabaya dan cukup banyak harta yang masih dihimpun atas nama Stiehting Waqfiah baik yang berada di tingkat pusat di Surabaya maupun ditempat lainnya. Salah satunya yang tetap dipelihara dengan baik dan tetap dalam "Pengawasan Nahdlatul Ulama'" sampai dengan sekarang yaitu tanah dan gedung Stiehting Waqfiah Nahdlatul Ulama' di Bangil Kabupaten Pasuruan, dan sampai sekarang nama gedung itu bernama "Gedung Waqfiah".</p>
<h3>Sejarah Singkat</h3>
<p>Pengurusan dan pengelolaan harta wakaf sejak awal kelahiran Nahdlatul Ulama sudah menjadi program yang dikelola dengan Management yang cermat dan rapi dan langsung dipimpin oleh Rois Akbar PBNU KH. Hasyim Asyari.<br>Sebuah dokumen autentik berupa STATUTEN dan Reglement Stiehting Waqfiah telah dibuat pada tanggal 23 Pebruari 1937 dihadapan notaris Hendrik Wiliem Nazembreg notaris Surabaya, yang terdiri dari 11 pasal atau artikel, yang disalah satu pasalnya menyebutkan : bahwa perhimpunan Nahdlatul Ulama secara resmi mendirikan Dewan Pengurus Wakaf, sebagai Rois adalah KH. Hasyim Asyari, dan sebagai Katib adalah KH. Wahab Hasbullah.<br>Keberadaan stiehting Waqfiah Nahdlatul Ulama, telah dilengkapi dengan anggaran rumah tangga yang terdiri dari 31 pasal, dan diantara pasalnya menyebutkan bahwa harta wakaf boleh diambil buah atau hasilnya untuk kepentingan umum.<br>Responsi yang tinggi dikalangan para pendiri Nahdlatul Ulama tersebut terhadap kegiatan perwakafan tersebut, maka Anggaran Rumah Tangga Stiehting Waqfiah Nahdlatul Ulama tersebut disetujui secara resmi oleh Konggres Perhimpunan Nahdlatul Ulama ke 14 pada tanggal 4-5 Juli 1939 di Magelang dan dijadikan Plant Nahdlatul Ulama secara Nasional.<br>Struktur Stiehting Waqfiah dari Mutamar Nahdlatul Ulama ke Mutamar yang lain mengalami reposisi struktur, namun nama tetap stiehting waqfiah. Di zaman Muktamar Nahdlatul Ulama ke 15 pada tanggal 10-15 Desember 1940, posisi Stiehting Waqfiah berada dibawah bagian harta yang langsung dalam pembinaan dan pengawasan Syuriyah.<br>Perkembangan selanjutnya nama Stiehting Waqfiah menjadi sub unit tersendiri yang berkedudukan di Surabaya dan cukup banyak harta yang masih dihimpun atas nama Stiehting Waqfiah baik yang berada di tingkat pusat di Surabaya maupun ditempat lainnya, salah satu diantaranya tetap dipelihara dengan baik dan tetap dalam pengawasan Nahdlatul Ulama sampai dengan sekarang yaitu tanah dan gedung Stiehting Waqfiah Nahdlatul Ulama di Bangil Kabupaten Pasuruan, dan sampai sekarangpun nama gedung itu bernama Gedung Waqfiah.<br>Ketika Mutamar Nahdlatul Ulama ke 28 pada tanggal 25-28 Nopember 1989 di Yogyakarta, posisi struktur Stiehting Waqfiah, masuk perangkat berbentuk Lajnah dan namanya berubah menjadi Lajnah waqfiah yang tugas pokoknya tetap sebagaimana tugas Stiehting. Nama dan posisi sebagai Lajnah Waqfiah pada saat Mutamar Nahdlatul Ulama ke 29 tanggal 1-5 Desember 1994 di Cipasung dimasukan dalam pasal 16 ayat 2 (e) Anggaran Rumah Tangga, yang bertugas untuk mengurus tanah dan bangunan yang diwakafkan kepada Nahdlatul Ulama.<br>Ketika Muktamar ke 30 pada tanggal 21-26 Nopember 1999 di Lirboyo Kediri, nama Lajnah berubah menjadi AUQOF, jamak dari waqfiah, namun tugas dan wewenangnya tidak berubah. dalam Mutamar di Kediri terdapat keputusan rekomendasi yang di tujukan kepada PBNU agar Stiehting Waqfiah yang kini di tangan perorangan Nahdlatul Ualma agar di bubarkan secara formal, sehingga tidak ada dualisme struktur.<br>Selanjutnya ketika Muktamar ke-31 pada tanggal 28 Nopember 2 Desember 2004 di Boyolali Jawa Tengah, Lajnah Auqof diubah menjadi Lembaga Wakaf dan Pertanahan Nahdlatul Ulama disingkat LWPNU yang bertugas mengurus, mengelola serta mengembangkan tanah dan bangunan serta harta benda wakaf lainnya milik Nahdlatul Ulama.</p>
<p>Pada Muktamar ke-35 Nahdlatul Ulama yang diselenggarakan di Pondok Pesantren (Ponpes) Tambak Beras, Jombang, diputuskan pergantian nama LWP menjadi <strong>LAW (Lembaga Aset dan Wakaf)</strong>.</p>
<p><strong>TANAH MILIK &amp; WAKAF AN. PWNU JAWA TIMUR</strong></p>
<ol>
<li>Tanah Kantor PWNU Jawa Timur, Jl. Raya Darmo 96<br>Status Hak Guna Bangunan, sudah habis masa penggunaannya selama 20 tahun dan<br>PWNU sudah memperoleh peningkatan hak dari HGB menjadi hak milik atas nama<br>PBNU dengan nomor Sertifikat 887 tahun 2005. Saat ini dipakai TV9.</li>
<li>Tanah Ledug Prigen Pasuruan (Balai Diklat PWNU Jawa Timur)<br>Luas tanah keseluruhan 7.380 M².<br>Status tanah tersebut saat ini telah terbit setipikat tanah Wakaf dengan Nadzir<br>Badan Hukum Perkumpulan Nahdlatul Ulama</li>
<li>Tanah Driyorejo Gresik<br>Tanah terletak di desa Wedoro Anom Kec. Driyorejo, Kab. Gresik seluas 3.500 M²<br>Telah terbit sertipikat Hak Milik Nomor 292 dan NIB. 12.09.02.14.00414 an.<br>Nahdlatul Ulama dengan luas 3.171 m2.</li>
<li>Tanah di Mojosari Driyorejo Gresik<br>Tanah terletak di desa Mojosari, Kec. Driyorejo, Kab. Gresik Telah terbit sertipikat<br>Hak Milik Nomor 724 dan NIB. 12.09.02.13.04432 an. Nahdlatul Ulama dengan luas<br>4.059 m2</li>
<li>Tanah 460 M2 di Jl. Citarum Surabaya<br>Tanah PWNU di jl. Citarum yang merupakan tanah eigendom verponding No.9966<br>atas nama Stichting Ganaamd bekas tanah milik perusahaan Belanda sehingga untuk<br>proses sertipikat an. Nahdlatul Ulama membutuhkan proses panjang dan sampai<br>saat ini berkas di Kantor BPN Jawa Timur</li>
<li>Tanah Gayungan I Surabaya<br>Yang kini dipakai Kantor PWNU Jawa Timur, sudah berstatus Sertipikat di<br>Gayungan atas nama Hak Milik NU nomor : 1.111, ditempati Kantor PWNU Jawa Timur</li>
<li>Tanah Gayungan II Surabaya<br>Yang terletak sebelah kiri Kantor PWNU, saat ini sudah bersertipikat an. Badan Hukum<br>Nahdlatul Ulama</li>
<li>Tanah Wakaf di Perumahan Gununganyar Surabaya seluas 400 M2<br>Tanah terletak Perumahan Gununganyar sampai saat ini telah terbit sertipikat<br>Tanah Wakaf dengan Nadzir Badan Hukum Perkumpulan Nahdlatul Ulama</li>
</ol>
<p>Sumber: <a href="https://lwpnu.or.id/sejarah" target="_blank" rel="noopener">lwpnu.or.id</a></p>
HTML;

        $strukturOrganisasiContent = <<<'HTML'
<h3>LWPNU Jawa Timur Masa Khidmat 2024-2029</h3>
<p>Struktur Organisasi</p>
<p><strong>Penasihat:</strong></p>
<ul>
<li>H. Saiful Munir, S.H., M.Kn.</li>
<li>KH. A. Panji Taufiq</li>
<li>KH. Ruhu Syahid Thoha, S.Pd.I</li>
<li>Prof. Dr. H. Ali Mas'ud Kholqillah, M. Ag</li>
</ul>
<p><strong>Ketua:</strong> H. Shodikun A. Karim, S.H., M.Kn.</p>
<p><strong>Wakil Ketua:</strong></p>
<ul>
<li>H. Moch. Irhas</li>
<li>H. Ahmad Kholiq, S.E.</li>
<li>H. Mohamad Arif Fauzi, S.HI., M.H.</li>
<li>Dr. H. Moh. Arwani, M.Ag, M.Hi.</li>
<li>Dr. H. Pardi, M. Pd. I.</li>
</ul>
<p><strong>Sekretaris:</strong> H. Nurvan Indra Praja, S.Kom., M.MT.</p>
<p><strong>Wakil Sekretaris:</strong></p>
<ul>
<li>Dr. H. Moh Imron Rosyadi, S.Ag., M.HI.</li>
<li>Muhammad Arif Widodo, S.T., M.SE.</li>
<li>Lukman Hakim, S.Fil. MT.</li>
<li>H. Abdul Wahid Al-Faizi, M. E.</li>
<li>Muh. Rizal, S.SIT, MH.</li>
</ul>
<p><strong>Bendahara:</strong> Ahmad Syakur, S.Pd.I.</p>
<p><strong>Wakil Bendahara:</strong></p>
<ul>
<li>Ahmada Nurul Huda, SH, M.Kn.</li>
<li>Sugeng Sudariyanto, S.Sos.</li>
</ul>
<p><strong>Anggota:</strong></p>
<ul>
<li>Paryono NA</li>
<li>Ahmad Fanani, S.Ag., M.Ag.</li>
<li>Abd. Rochim, S.Ag.</li>
<li>H. M. Ali Faiq, M.HI.</li>
<li>Dr. H. Moh. Sholehudin, M.Ag.</li>
<li>H. Moh. Ali, M.HI.</li>
<li>Firda Lailul Fitria, S.Kom.</li>
<li>M. Imdadur Rohman, S.HI., M.HI.</li>
<li>Ahmad Trisno, S.H., M.H.</li>
<li>Fattahul Anjab, S.Hi., CPL.</li>
<li>Muhammad Haris Izzudin</li>
<li>M. Attabik Usman, M.T.</li>
</ul>
HTML;

        $pages = [
            [
                'title' => 'Tentang LAWNU',
                'slug' => 'tentang-lawnu',
                'content' => $tentangLawnuContent,
                'status' => 'published',
                'published_at' => now(),
                'author_id' => $authorId,
            ],
            [
                'title' => 'Sejarah',
                'slug' => 'sejarah',
                'content' => '<p>Lembaga Wakaf dan Pertanahan (LWP) dibentuk sebagai bagian dari struktur organisasi Nahdlatul Ulama untuk mengelola dan mengembangkan aset-aset wakaf yang menjadi milik masyarakat.</p><p>Sejak dibentuk, LWP terus berkembang dalam menjalankan mandatnya untuk menjaga, mengelola, dan mengembangkan aset-aset wakaf agar memberikan manfaat bagi umat.</p>',
                'status' => 'published',
                'published_at' => now(),
                'author_id' => $authorId,
            ],
            [
                'title' => 'Visi & Misi',
                'slug' => 'visi-misi',
                'content' => '<h2>Visi</h2><p>Menjadi lembaga wakaf dan pertanahan yang amanah, profesional, dan berkelanjutan dalam pengelolaan aset wakaf Nahdlatul Ulama.</p><h2>Misi</h2><ul><li>Pendataan dan pencatatan aset wakaf secara terstruktur</li><li>Pengamanan hukum atas aset wakaf</li><li>Pengembangan dan pemberdayaan aset wakaf</li><li>Pendampingan nadzir dalam pengelolaan wakaf</li><li>Advokasi kebijakan pro-wakaf</li></ul>',
                'status' => 'published',
                'published_at' => now(),
                'author_id' => $authorId,
            ],
            [
                'title' => 'Struktur Organisasi',
                'slug' => 'struktur-organisasi',
                'content' => $strukturOrganisasiContent,
                'status' => 'published',
                'published_at' => now(),
                'author_id' => $authorId,
            ],
            [
                'title' => 'Program Kerja',
                'slug' => 'program',
                'content' => '<p>Program kerja LWP PWNU meliputi:</p><ul><li>Pendataan aset wakaf dan pertanahan</li><li>Legalisasi dan sertifikasi aset wakaf</li><li>Pelatihan dan pendampingan nadzir</li><li>Pengembangan wakaf produktif</li><li>Advokasi kebijakan wakaf</li><li>Publikasi dan sosialisasi</li></ul>',
                'status' => 'published',
                'published_at' => now(),
                'author_id' => $authorId,
            ],
            [
                'title' => 'Galeri',
                'slug' => 'galeri',
                'content' => '<p>Kumpulan dokumentasi foto kegiatan LWP PWNU Jatim.</p>',
                'status' => 'published',
                'published_at' => now(),
                'author_id' => $authorId,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
