<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Card;
use App\Models\Chapter;
use App\Models\Clip;
use App\Models\Deck;
use App\Models\GrammarPattern;
use App\Models\JlptTarget;
use App\Models\PlanTask;
use App\Models\ScheduleItem;
use App\Models\TranslationExercise;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@nihon.test'],
            [
                'name' => 'Mahasiswa Sastra Jepang',
                'password' => 'password',
                'jlpt_level' => 'N3',
                'university' => 'Universitas Indonesia',
            ]
        );

        $this->seedSrs($user);
        $this->seedReading($user);
        $this->seedGrammar($user);
        $this->seedTranslation($user);
        $this->seedAgenda($user);
        $this->seedFibUndipFeatures($user);
    }

    protected function seedSrs(User $user): void
    {
        $deckKanji = Deck::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Kanji N3 Inti'],
            [
                'description' => 'Kanji yang sering muncul di teks sastra',
                'color' => '#E8604C',
                'card_type' => 'kanji',
            ]
        );

        $deckKosa = Deck::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Kosakata Sastra'],
            [
                'description' => 'Kosakata dari novel & puisi',
                'color' => '#4F6EF7',
                'card_type' => 'kosakata',
            ]
        );

        $kanjiData = [
            ['front' => '秋', 'readings' => ['on' => 'シュウ', 'kun' => 'あき'], 'meaning' => 'musim gugur', 'example_sentence' => '秋の空は美しい。', 'example_translation' => 'Langit musim gugur itu indah.', 'tags' => ['sastra', 'musim']],
            ['front' => '夜', 'readings' => ['on' => 'ヤ', 'kun' => 'よる'], 'meaning' => 'malam', 'example_sentence' => '夜が明ける。', 'example_translation' => 'Hari mulai terang.', 'tags' => ['sastra']],
            ['front' => '心', 'readings' => ['on' => 'シン', 'kun' => 'こころ'], 'meaning' => 'hati, batin', 'example_sentence' => '心の声を聞く。', 'example_translation' => 'Mendengar suara hati.', 'tags' => ['sastra']],
            ['front' => '風', 'readings' => ['on' => 'フウ', 'kun' => 'かぜ'], 'meaning' => 'angin', 'example_sentence' => '風が静かに吹く。', 'example_translation' => 'Angin bertiup pelan.', 'tags' => ['sastra', 'alam']],
            ['front' => '夢', 'readings' => ['on' => 'ム', 'kun' => 'ゆめ'], 'meaning' => 'mimpi', 'example_sentence' => '夢は消えない。', 'example_translation' => 'Mimpi tidak hilang.', 'tags' => ['sastra']],
        ];

        $kosaData = [
            ['front' => '懐かしい', 'readings' => null, 'meaning' => 'nostalgis, teringat masa lalu', 'example_sentence' => '懐かしい写真を見つけた。', 'example_translation' => 'Aku menemukan foto yang membuatku nostalgia.', 'tags' => ['adj-i']],
            ['front' => '儚い', 'readings' => null, 'meaning' => 'fana, singkat', 'example_sentence' => '人生は儚いものだ。', 'example_translation' => 'Hidup itu fana.', 'tags' => ['adj-i', 'sastra']],
            ['front' => '静寂', 'readings' => ['on' => 'セイジャク'], 'meaning' => 'kesunyian', 'example_sentence' => '夜の静寂が広がる。', 'example_translation' => 'Kesunyian malam menyebar.', 'tags' => ['noun']],
            ['front' => '回想', 'readings' => ['on' => 'カイソウ'], 'meaning' => 'kenangan, kilas balik', 'example_sentence' => '昔を回想する。', 'example_translation' => 'Mengenang masa lalu.', 'tags' => ['noun', 'sastra']],
            ['front' => '切ない', 'readings' => null, 'meaning' => 'pilu, menyayat hati', 'example_sentence' => '別れは切ない。', 'example_translation' => 'Perpisahan itu pilu.', 'tags' => ['adj-i', 'sastra']],
        ];

        $this->insertCards($deckKanji, $kanjiData, $user);
        $this->insertCards($deckKosa, $kosaData, $user);
    }

    protected function insertCards(Deck $deck, array $rows, User $user): void
    {
        $existing = $deck->cards()->pluck('front')->all();

        foreach ($rows as $row) {
            if (in_array($row['front'], $existing, true)) {
                continue;
            }

            $due = Carbon::now()->subDays(rand(0, 3));

            $deck->cards()->create([
                'user_id' => $user->id,
                'front' => $row['front'],
                'readings' => $row['readings'],
                'meaning' => $row['meaning'],
                'example_sentence' => $row['example_sentence'],
                'example_translation' => $row['example_translation'],
                'tags' => $row['tags'],
                'due_date' => $due,
                'state' => rand(0, 1) ? 'review' : 'new',
                'repetition' => 1,
                'interval' => 1,
            ]);
        }
    }

    protected function seedReading(User $user): void
    {
        $book = Book::firstOrCreate(
            ['user_id' => $user->id, 'title' => '吾輩は猫である'],
            [
                'author' => 'Natsume Soseki',
                'author_jp' => '夏目漱石',
                'genre' => 'novel',
                'total_pages' => 150,
                'current_page' => 42,
                'status' => 'reading',
                'cover_color' => '#4F6EF7',
            ]
        );

        if ($book->chapters()->count() === 0) {
            $ch1 = $book->chapters()->create(['title' => '第一章', 'sort_order' => 1, 'page_start' => 1, 'page_end' => 30, 'is_completed' => true]);
            $ch2 = $book->chapters()->create(['title' => '第二章', 'sort_order' => 2, 'page_start' => 31, 'page_end' => 60, 'is_completed' => false]);

            $ch1->notes()->create(['page_no' => 12, 'content' => 'Soseki memakai sudut pandang kucing; gaya humor satir terhadap kaum intelektual Meiji.']);
            $ch2->notes()->create(['page_no' => 35, 'content' => 'Bagian kritik terhadap guru Kusama mulai muncul.']);
        }

        $chapter = $book->chapters()->first();

        Clip::firstOrCreate(
            ['user_id' => $user->id, 'expression' => '徘徊する'],
            [
                'reading' => 'はいかいする',
                'meaning' => 'berkelana, mondar-mandir tanpa tujuan',
                'context_sentence' => '吾輩は家の裏で徘徊する猫である。',
                'translation' => 'Aku adalah kucing yang berkelana di belakang rumah.',
                'book_id' => $book->id,
                'chapter_id' => $chapter?->id,
            ]
        );

        Clip::firstOrCreate(
            ['user_id' => $user->id, 'expression' => '縁側'],
            [
                'reading' => 'えんがわ',
                'meaning' => 'beranda kayu rumah Jepang',
                'context_sentence' => '縁側に寝そべっている。',
                'translation' => 'Berbaring santai di beranda kayu.',
                'book_id' => $book->id,
                'chapter_id' => $chapter?->id,
            ]
        );

        $haikuBook = Book::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'おくのほそ道'],
            [
                'author' => 'Matsuo Basho',
                'author_jp' => '松尾芭蕉',
                'genre' => 'puisi',
                'total_pages' => 80,
                'current_page' => 0,
                'status' => 'to_read',
                'cover_color' => '#E8604C',
            ]
        );
    }

    protected function seedGrammar(User $user): void
    {
        GrammarPattern::firstOrCreate(
            ['user_id' => $user->id, 'pattern' => '〜てしまう'],
            [
                'meaning' => 'menyatakan selesai / penyesalan',
                'structure' => '動詞て形 + しまう',
                'usage' => 'Menyatakan tindakan telah selesai (tak disengaja) atau ada rasa penyesalan.',
                'examples' => [
                    ['jp' => '宿題を忘れてしまった。', 'id' => 'Aku lupa PR.'],
                    ['jp' => '本を読んでしまった。', 'id' => 'Buku itu sudah habis kubaca.'],
                ],
                'tags' => ['N4', 'pola umum'],
            ]
        );

        GrammarPattern::firstOrCreate(
            ['user_id' => $user->id, 'pattern' => '〜たり 〜たりする'],
            [
                'meaning' => 'melakukan hal ini dan itu',
                'structure' => '動詞た形 + り、動詞た形 + り + する',
                'usage' => 'Mencantumkan beberapa contoh tindakan.',
                'examples' => [
                    ['jp' => '本を読んだり、散歩したりする。', 'id' => 'Kadang membaca, kadang jalan-jalan.'],
                ],
                'tags' => ['N5', 'pola umum'],
            ]
        );

        GrammarPattern::firstOrCreate(
            ['user_id' => $user->id, 'pattern' => '〜ぬ (助動詞)'],
            [
                'meaning' => 'negasi klasik (bungo)',
                'structure' => '未然形 + ぬ',
                'usage' => 'Bentuk negatif dalam bahasa klasik (bungo). Dalam sastra modern biasanya muncul sebagai "あるまい" atau "見えぬ".',
                'examples' => [
                    ['jp' => '見えぬ星もある。', 'id' => 'Ada pula bintang yang tak terlihat.'],
                ],
                'is_bungo' => true,
                'tags' => ['bungo', 'sastra klasik'],
            ]
        );

        GrammarPattern::firstOrCreate(
            ['user_id' => $user->id, 'pattern' => '〜けり (助動詞)'],
            [
                'meaning' => 'past tense / ingatan klasik',
                'structure' => '連用形 + けり',
                'usage' => 'Menyatakan lampau atau kenangan; sering muncul di monogatari dan haiku.',
                'examples' => [
                    ['jp' => '昔ありけり。', 'id' => 'Pada zaman dahulu, ada (kata orang).'],
                ],
                'is_bungo' => true,
                'tags' => ['bungo', 'sastra klasik'],
            ]
        );
    }

    protected function seedTranslation(User $user): void
    {
        $exercise = TranslationExercise::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Puisi pendek - 秋の夜'],
            [
                'source_text' => "秋の夜\n虫の声が\n遠くで聞こえる。",
                'source_lang' => 'ja',
                'target_lang' => 'id',
                'my_translation' => "Malam musim gugur\nSuara serangga\nterdengar dari kejauhan.",
                'status' => 'in_progress',
            ]
        );

        if ($exercise->revisions()->count() === 0) {
            $exercise->revisions()->create(['content' => "Malam musim gugur\nSuara serangga\nterdengar dari kejauhan."]);
            $exercise->revisions()->create(['content' => "Malam musim gugur\nDerik serangga\nmemanggil dari jauh."]);
        }
    }

    protected function seedAgenda(User $user): void
    {
        ScheduleItem::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Kuliah Nihongo Chukyu 2'],
            [
                'date' => Carbon::today()->toDateString(),
                'time' => '08:00',
                'type' => 'kuliah',
                'course' => 'Nihongo Chukyu 2',
                'location' => 'Ruang 402',
                'priority' => 'high',
            ]
        );

        ScheduleItem::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Deadline esai Soseki'],
            [
                'date' => Carbon::today()->addDays(3)->toDateString(),
                'type' => 'deadline',
                'course' => 'Sastra Modern Jepang',
                'priority' => 'high',
            ]
        );

        ScheduleItem::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Latihan honyaku (terjemahan)'],
            [
                'date' => Carbon::today()->addDays(1)->toDateString(),
                'type' => 'tugas',
                'course' => 'Honyaku Nyumon',
                'priority' => 'medium',
            ]
        );

        $target = JlptTarget::firstOrCreate(
            ['user_id' => $user->id, 'level' => 'N3', 'is_active' => true],
            [
                'title' => 'JLPT N3 Desember',
                'target_date' => Carbon::now()->addMonths(4)->toDateString(),
            ]
        );

        if ($target->checklistItems()->count() === 0) {
            foreach (['Kanji N3 (600)', 'Kosakata N3 (1200)', 'Bunpou N3', 'Dokkai (membaca)', 'Choukai (mendengar)'] as $item) {
                $target->checklistItems()->create(['name' => $item]);
            }
            $target->checklistItems()->first()->update(['is_done' => true]);
        }

        PlanTask::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Review 20 kanji baru'],
            ['column' => 'doing', 'order' => 0, 'due_date' => Carbon::today()->toDateString()]
        );

        PlanTask::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Baca bab 2 吾輩は猫である'],
            ['column' => 'todo', 'order' => 1, 'due_date' => Carbon::today()->addDays(2)->toDateString()]
        );

        PlanTask::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Clip 10 frasa dari teks'],
            ['column' => 'todo', 'order' => 2, 'due_date' => Carbon::today()->addDays(3)->toDateString()]
        );

        PlanTask::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Latihan honyaku 1 kalimat panjang'],
            ['column' => 'done', 'order' => 0, 'due_date' => Carbon::today()->toDateString()]
        );
    }

    protected function seedFibUndipFeatures(User $user): void
    {
        // 1. Jadwal Kuliah Mahasiswa FIB UNDIP
        $classes = [
            [
                'subject' => 'Sastra Jepang Modern (近代日本文学)',
                'code' => 'FIB-JP301',
                'lecturer' => 'Dr. Budi Santoso, M.Hum.',
                'room' => 'Gedung A Lt. 2 Ruang 204 FIB UNDIP',
                'day_of_week' => 1, // Senin
                'start_time' => '08:00:00',
                'end_time' => '09:40:00',
                'credits' => 3,
                'reminder_minutes' => 120, // 2 Jam Sebelum Kuliah
                'color' => '#E11D48',
                'notes' => 'Membahas bab 2 novel Wagahai wa Neko de Aru karya Natsume Soseki.',
            ],
            [
                'subject' => 'Honyaku & Penerjemahan Teks Sastra (翻訳演習)',
                'code' => 'FIB-JP304',
                'lecturer' => 'Dewi Lestari, M.A.',
                'room' => 'Lab Bahasa FIB Lt. 1 Ruang L-02',
                'day_of_week' => 2, // Selasa
                'start_time' => '10:00:00',
                'end_time' => '11:40:00',
                'credits' => 2,
                'reminder_minutes' => 120,
                'color' => '#4F6EF7',
                'notes' => 'Praktik menerjemahkan cerpen Rashomon karya Akutagawa Ryunosuke.',
            ],
            [
                'subject' => 'Tata Bahasa & Bungo Klasik (古典文法・文語)',
                'code' => 'FIB-JP308',
                'lecturer' => 'Prof. Kenji Takahashi, Ph.D.',
                'room' => 'Gedung B Lt. 3 Ruang B-301 FIB UNDIP',
                'day_of_week' => 3, // Rabu
                'start_time' => '13:00:00',
                'end_time' => '14:40:00',
                'credits' => 3,
                'reminder_minutes' => 120,
                'color' => '#8B5CF6',
                'notes' => 'Materi partikel pembantu keri, tari, dan bungo jokotoba.',
            ],
            [
                'subject' => 'Dokkai & Analisis Teks Sastra Jepang (読解分析)',
                'code' => 'FIB-JP310',
                'lecturer' => 'Siti Rahmawati, M.Hum.',
                'room' => 'Gedung D Lt. 2 Ruang D-205 FIB UNDIP',
                'day_of_week' => 4, // Kamis
                'start_time' => '08:00:00',
                'end_time' => '09:40:00',
                'credits' => 2,
                'reminder_minutes' => 120,
                'color' => '#10B981',
                'notes' => 'Membawa print-out esai zuihitsu Tsurezuregusa.',
            ],
            [
                'subject' => 'Sejarah & Kebudayaan Jepang (日本史・文化論)',
                'code' => 'FIB-JP205',
                'lecturer' => 'Hendra Prasetyo, M.Si.',
                'room' => 'Auditorium Gedung Dekanat FIB UNDIP',
                'day_of_week' => 5, // Jumat
                'start_time' => '09:00:00',
                'end_time' => '10:40:00',
                'credits' => 2,
                'reminder_minutes' => 120,
                'color' => '#F59E0B',
                'notes' => 'Presentasi kelompok era Meiji & Taisho.',
            ],
        ];

        foreach ($classes as $c) {
            \App\Models\ClassSchedule::firstOrCreate(
                ['user_id' => $user->id, 'subject' => $c['subject'], 'day_of_week' => $c['day_of_week']],
                $c
            );
        }

        // 2. Agenda / Catatan Diary Kampus
        $diaries = [
            [
                'title' => 'Refleksi Membedah Gaya Bahasa Natsume Soseki di Perpustakaan FIB',
                'content' => "Hari ini menghabiskan waktu 3 jam di lantai 2 Perpustakaan FIB UNDIP membaca ulang novel Wagahai wa Neko de Aru. Menemukan banyak diksi bernuansa satir yang sangat khas era Meiji. Sudah memasukkan 5 kata baru ke deck SRS!",
                'entry_date' => Carbon::now()->subDays(1)->toDateString(),
                'mood' => 'fokus',
                'category' => 'kuliah',
                'tags' => ['sastra', 'soseki', 'perpustakaan', 'fib-undip'],
                'is_pinned' => true,
            ],
            [
                'title' => 'Rapat Perdana Panitia Festival Budaya Jepang (Bunkasai FIB 2026)',
                'content' => "Kumpul bareng teman-teman Himpunan Mahasiswa Sastra Jepang FIB UNDIP di Gazebo Gedung A untuk membahas konsep stan kebudayaan, lomba speech contest, dan pameran kaligrafi Shodou. Semangat!",
                'entry_date' => Carbon::now()->subDays(3)->toDateString(),
                'mood' => 'semangat',
                'category' => 'organisasi',
                'tags' => ['bunkasai', 'organisasi', 'himpunan', 'himasaja'],
                'is_pinned' => false,
            ],
            [
                'title' => 'Bimbingan Skripsi Penerjemahan bersama Dr. Budi',
                'content' => "Mendapat feedback berharga mengenai pergeseran makna pada terjemahan idiom yojijukugo. Dosen menyarankan untuk memperbanyak referensi kamus kanji klasik.",
                'entry_date' => Carbon::now()->subDays(6)->toDateString(),
                'mood' => 'produktif',
                'category' => 'bimbingan',
                'tags' => ['skripsi', 'bimbingan', 'honyaku'],
                'is_pinned' => false,
            ],
        ];

        foreach ($diaries as $d) {
            \App\Models\CampusDiary::firstOrCreate(
                ['user_id' => $user->id, 'title' => $d['title']],
                $d
            );
        }

        // 3. Foto & Album Timeline Kampus
        $photos = [
            [
                'title' => 'Kemeriahan Festival Bunkasai Sastra Jepang FIB UNDIP',
                'description' => 'Dokumentasi penampilan Shodou Performance dan parade Yukata mahasiswa Prodi Sastra Jepang di pelataran Gedung Dekanat FIB Universitas Diponegoro.',
                'photo_url' => 'https://images.unsplash.com/photo-1528164344705-475426879c0d?w=800&q=80',
                'event_date' => Carbon::now()->subDays(5)->toDateString(),
                'location' => 'Pelataran Gedung Dekanat FIB UNDIP Tembalang',
                'category' => 'bunkasai',
                'likes_count' => 48,
                'is_public' => true,
            ],
            [
                'title' => 'Kuliah Umum & Bedah Sastra Klasik Jepang di Teater Lingkar',
                'description' => 'Sesi diskusi interaktif mengenai estetika Wabi-Sabi dalam karya sastra era Heian bersama dosen tamu dari Osaka University.',
                'photo_url' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800&q=80',
                'event_date' => Carbon::now()->subDays(12)->toDateString(),
                'location' => 'Teater Lingkar Lt. 1 FIB UNDIP',
                'category' => 'seminar',
                'likes_count' => 35,
                'is_public' => true,
            ],
            [
                'title' => 'Diskusi Belajar Kelompok Kanji & Honyaku di Gazebo Rindang FIB',
                'description' => 'Mempersiapkan ujian JLPT dan tugas terjemahan cerpen sastra bareng teman seangkatan.',
                'photo_url' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=800&q=80',
                'event_date' => Carbon::now()->subDays(18)->toDateString(),
                'location' => 'Gazebo Halaman Gedung B FIB UNDIP',
                'category' => 'kuliah',
                'likes_count' => 29,
                'is_public' => true,
            ],
        ];

        foreach ($photos as $p) {
            \App\Models\CampusPhoto::firstOrCreate(
                ['user_id' => $user->id, 'title' => $p['title']],
                $p
            );
        }
    }
}
