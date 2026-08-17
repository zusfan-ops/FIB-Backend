<?php

namespace Database\Seeders;

use App\Models\DictionaryEntry;
use Illuminate\Database\Seeder;

class DictionaryEntrySeeder extends Seeder
{
    public function run(): void
    {
        if (DictionaryEntry::count() > 0) {
            return;
        }

        foreach ($this->entries() as $entry) {
            DictionaryEntry::create($entry);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function entries(): array
    {
        $kosakata = fn (string $term, ?string $kun, ?string $on, string $meaning, ?string $ex, ?string $exId, string $level) => [
            'term' => $term,
            'reading_kun' => $kun,
            'reading_on' => $on,
            'meaning' => $meaning,
            'example_sentence' => $ex,
            'example_translation' => $exId,
            'category' => 'kosakata',
            'jlpt_level' => $level,
        ];

        $kanji = fn (string $term, ?string $kun, ?string $on, string $meaning, string $level) => [
            'term' => $term,
            'reading_kun' => $kun,
            'reading_on' => $on,
            'meaning' => $meaning,
            'example_sentence' => null,
            'example_translation' => null,
            'category' => 'kanji',
            'jlpt_level' => $level,
        ];

        return [
            // ===== N5 =====
            $kosakata('食べる', 'たべる', null, 'makan', '朝ご飯を食べる。', 'Makan sarapan.', 'N5'),
            $kosakata('飲む', 'のむ', null, 'minum', '水を飲む。', 'Minum air.', 'N5'),
            $kosakata('学校', 'がっこう', null, 'sekolah', '学校へ行く。', 'Pergi ke sekolah.', 'N5'),
            $kosakata('先生', 'せんせい', null, 'guru / dosen', '先生に聞く。', 'Bertanya kepada guru.', 'N5'),
            $kosakata('学生', 'がくせい', null, 'siswa / mahasiswa', '私は学生です。', 'Saya adalah mahasiswa.', 'N5'),
            $kosakata('友達', 'ともだち', null, 'teman', '友達と話す。', 'Berbicara dengan teman.', 'N5'),
            $kosakata('本', 'ほん', null, 'buku', '本を読む。', 'Membaca buku.', 'N5'),
            $kosakata('猫', 'ねこ', null, 'kucing', '猫がいる。', 'Ada kucing.', 'N5'),
            $kosakata('犬', 'いぬ', null, 'anjing', '犬と遊ぶ。', 'Bermain dengan anjing.', 'N5'),
            $kosakata('大きい', 'おおきい', null, 'besar', 'この家は大きい。', 'Rumah ini besar.', 'N5'),
            $kosakata('小さい', 'ちいさい', null, 'kecil', 'この猫は小さい。', 'Kucing ini kecil.', 'N5'),
            $kosakata('行く', 'いく', null, 'pergi', '大学に行く。', 'Pergi ke universitas.', 'N5'),
            $kosakata('来る', 'くる', null, 'datang', '友達が来る。', 'Teman datang.', 'N5'),
            $kosakata('見る', 'みる', null, 'melihat / menonton', '映画を見る。', 'Menonton film.', 'N5'),
            $kosakata('聞く', 'きく', null, 'mendengar / bertanya', '音楽を聞く。', 'Mendengarkan musik.', 'N5'),
            $kosakata('話す', 'はなす', null, 'berbicara', '日本語を話す。', 'Berbicara bahasa Jepang.', 'N5'),
            $kosakata('読む', 'よむ', null, 'membaca', '新聞を読む。', 'Membaca koran.', 'N5'),
            $kosakata('書く', 'かく', null, 'menulis', '手紙を書く。', 'Menulis surat.', 'N5'),
            $kanji('日', 'ひ', 'にち・じつ', 'hari / matahari', 'N5'),
            $kanji('月', 'つき', 'げつ・がつ', 'bulan', 'N5'),
            $kanji('火', 'ひ', 'か', 'api', 'N5'),
            $kanji('水', 'みず', 'すい', 'air', 'N5'),
            $kanji('木', 'き', 'もく・ぼく', 'pohon / kayu', 'N5'),
            $kanji('金', 'かね', 'きん', 'emas / uang', 'N5'),
            $kanji('土', 'つち', 'ど・と', 'tanah', 'N5'),
            $kanji('人', 'ひと', 'じん・にん', 'orang', 'N5'),
            $kanji('山', 'やま', 'さん', 'gunung', 'N5'),
            $kanji('川', 'かわ', 'せん', 'sungai', 'N5'),

            // ===== N4 =====
            $kosakata('会議', 'かいぎ', null, 'rapat', '会議に出る。', 'Menghadiri rapat.', 'N4'),
            $kosakata('準備', 'じゅんび', null, 'persiapan', '準備をする。', 'Melakukan persiapan.', 'N4'),
            $kosakata('説明', 'せつめい', null, 'penjelasan', '説明を聞く。', 'Mendengarkan penjelasan.', 'N4'),
            $kosakata('予定', 'よてい', null, 'rencana / jadwal', '明日の予定がある。', 'Ada rencana besok.', 'N4'),
            $kosakata('経験', 'けいけん', null, 'pengalaman', 'いい経験になった。', 'Menjadi pengalaman yang baik.', 'N4'),
            $kosakata('便利', 'べんり', null, 'praktis / nyaman', 'このアプリは便利だ。', 'Aplikasi ini praktis.', 'N4'),
            $kosakata('心配', 'しんぱい', null, 'khawatir', '試験が心配だ。', 'Khawatir tentang ujian.', 'N4'),
            $kosakata('約束', 'やくそく', null, 'janji', '約束を守る。', 'Menepati janji.', 'N4'),
            $kosakata('機会', 'きかい', null, 'kesempatan', 'いい機会だ。', 'Ini kesempatan yang baik.', 'N4'),
            $kosakata('相談', 'そうだん', null, 'konsultasi / musyawarah', '先生に相談する。', 'Berkonsultasi dengan dosen.', 'N4'),
            $kosakata('到着', 'とうちゃく', null, 'tiba', '駅に到着する。', 'Tiba di stasiun.', 'N4'),
            $kosakata('出発', 'しゅっぱつ', null, '(ber)angkat', '朝早く出発する。', 'Berangkat pagi-pagi.', 'N4'),
            $kosakata('参加', 'さんか', null, 'partisipasi', 'イベントに参加する。', 'Berpartisipasi dalam acara.', 'N4'),
            $kosakata('変化', 'へんか', null, 'perubahan', '天気の変化が激しい。', 'Perubahan cuaca sangat drastis.', 'N4'),
            $kosakata('習慣', 'しゅうかん', null, 'kebiasaan', '日本の習慣を学ぶ。', 'Mempelajari kebiasaan Jepang.', 'N4'),

            // ===== N3 =====
            $kosakata('環境', 'かんきょう', null, 'lingkungan', '環境を守る。', 'Menjaga lingkungan.', 'N3'),
            $kosakata('影響', 'えいきょう', null, 'pengaruh / dampak', '社会に影響を与える。', 'Memberi pengaruh pada masyarakat.', 'N3'),
            $kosakata('提案', 'ていあん', null, 'usulan', '新しい提案をする。', 'Mengajukan usulan baru.', 'N3'),
            $kosakata('判断', 'はんだん', null, 'keputusan / penilaian', '正しい判断をする。', 'Membuat keputusan yang tepat.', 'N3'),
            $kosakata('責任', 'せきにん', null, 'tanggung jawab', '責任を持つ。', 'Memikul tanggung jawab.', 'N3'),
            $kosakata('印象', 'いんしょう', null, 'kesan', 'いい印象を与える。', 'Memberikan kesan yang baik.', 'N3'),
            $kosakata('効果', 'こうか', null, 'efek / hasil', '効果がある。', 'Ada efeknya.', 'N3'),
            $kosakata('状況', 'じょうきょう', null, 'situasi / keadaan', '状況を確認する。', 'Memeriksa situasi.', 'N3'),
            $kosakata('対応', 'たいおう', null, 'penanganan / respons', '問題に対応する。', 'Menangani masalah.', 'N3'),
            $kosakata('実現', 'じつげん', null, 'realisasi / terwujud', '夢を実現する。', 'Mewujudkan mimpi.', 'N3'),
            $kosakata('検討', 'けんとう', null, 'pertimbangan / kajian', 'よく検討する。', 'Mempertimbangkan dengan baik.', 'N3'),
            $kosakata('維持', 'いじ', null, 'pemeliharaan', '健康を維持する。', 'Menjaga kesehatan.', 'N3'),
            $kosakata('傾向', 'けいこう', null, 'kecenderungan', '増える傾向にある。', 'Ada kecenderungan meningkat.', 'N3'),
            $kosakata('発展', 'はってん', null, 'perkembangan', '経済が発展する。', 'Ekonomi berkembang.', 'N3'),
            $kosakata('特徴', 'とくちょう', null, 'ciri khas', '文体の特徴を分析する。', 'Menganalisis ciri khas gaya bahasa.', 'N3'),

            // ===== N2 =====
            $kosakata('概念', 'がいねん', null, 'konsep', '新しい概念を学ぶ。', 'Mempelajari konsep baru.', 'N2'),
            $kosakata('矛盾', 'むじゅん', null, 'kontradiksi', '話に矛盾がある。', 'Ada kontradiksi dalam ceritanya.', 'N2'),
            $kosakata('抽象', 'ちゅうしょう', null, 'abstrak', '抽象的な表現。', 'Ungkapan yang abstrak.', 'N2'),
            $kosakata('従来', 'じゅうらい', null, 'konvensional / selama ini', '従来の方法を見直す。', 'Meninjau ulang metode konvensional.', 'N2'),
            $kosakata('均衡', 'きんこう', null, 'keseimbangan', '均衡を保つ。', 'Menjaga keseimbangan.', 'N2'),
            $kosakata('顕著', 'けんちょ', null, 'mencolok / nyata', '顕著な変化が見られる。', 'Terlihat perubahan yang mencolok.', 'N2'),
            $kosakata('妥協', 'だきょう', null, 'kompromi', '妥協しない。', 'Tidak berkompromi.', 'N2'),
            $kosakata('促進', 'そくしん', null, 'pendorong / promosi', '交流を促進する。', 'Mendorong pertukaran budaya.', 'N2'),
            $kosakata('抑制', 'よくせい', null, 'penekanan / pengendalian', '感情を抑制する。', 'Mengendalikan emosi.', 'N2'),
            $kosakata('潜在', 'せんざい', null, 'laten / tersembunyi', '潜在的な能力。', 'Kemampuan yang tersembunyi.', 'N2'),
            $kosakata('遂行', 'すいこう', null, 'pelaksanaan', '任務を遂行する。', 'Melaksanakan tugas.', 'N2'),
            $kosakata('継承', 'けいしょう', null, 'pewarisan', '伝統文化を継承する。', 'Mewariskan budaya tradisional.', 'N2'),
            $kosakata('普及', 'ふきゅう', null, 'penyebarluasan', 'スマホが普及した。', 'Ponsel pintar telah menyebar luas.', 'N2'),
            $kosakata('洗練', 'せんれん', null, 'kehalusan / kecanggihan', '洗練された文章。', 'Tulisan yang halus dan elegan.', 'N2'),
            $kosakata('隆盛', 'りゅうせい', null, 'kejayaan', '文化の隆盛。', 'Kejayaan budaya.', 'N2'),

            // ===== N1 (termasuk istilah sastra klasik) =====
            $kosakata('曖昧', 'あいまい', null, 'ambigu / kabur', '曖昧な返事をする。', 'Memberi jawaban yang ambigu.', 'N1'),
            $kosakata('逸脱', 'いつだつ', null, 'penyimpangan', '規範から逸脱する。', 'Menyimpang dari norma.', 'N1'),
            $kosakata('概観', 'がいかん', null, 'gambaran umum', '歴史の概観を述べる。', 'Menjelaskan gambaran umum sejarah.', 'N1'),
            $kosakata('示唆', 'しさ', null, 'sugesti / petunjuk', '重要な示唆を与える。', 'Memberikan petunjuk penting.', 'N1'),
            $kosakata('帰属', 'きぞく', null, 'afiliasi / kepemilikan', '帰属意識を持つ。', 'Memiliki rasa kepemilikan.', 'N1'),
            $kosakata('培う', 'つちかう', null, 'memupuk / menumbuhkan', '感性を培う。', 'Menumbuhkan kepekaan rasa.', 'N1'),
            $kosakata('顧みる', 'かえりみる', null, 'merenungkan / menoleh ke belakang', '過去を顧みる。', 'Merenungkan masa lalu.', 'N1'),
            $kosakata('網羅', 'もうら', null, 'mencakup keseluruhan', '全分野を網羅する。', 'Mencakup seluruh bidang.', 'N1'),
            $kosakata('些細', 'ささい', null, 'sepele', '些細な問題だ。', 'Ini masalah yang sepele.', 'N1'),
            $kosakata('払拭', 'ふっしょく', null, 'menghapuskan', '不安を払拭する。', 'Menghapuskan kecemasan.', 'N1'),
            $kosakata('卓越', 'たくえつ', null, 'keunggulan', '卓越した才能。', 'Bakat yang unggul.', 'N1'),
            $kosakata('逐次', 'ちくじ', null, 'berurutan / bertahap', '逐次報告する。', 'Melaporkan secara bertahap.', 'N1'),
            $kosakata('邁進', 'まいしん', null, 'maju pantang mundur', '目標に向かって邁進する。', 'Maju pantang mundur menuju tujuan.', 'N1'),
            $kosakata('隠喩', 'いんゆ', null, 'metafora', '隠喩を用いた表現。', 'Ungkapan yang menggunakan metafora.', 'N1'),
            $kosakata('文語', 'ぶんご', null, 'bahasa sastra klasik (bungo)', '古典は文語で書かれている。', 'Karya klasik ditulis dalam bahasa sastra klasik.', 'N1'),
        ];
    }
}
