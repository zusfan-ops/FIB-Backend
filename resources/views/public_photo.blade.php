<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $photo->title }} — Dokumentasi FIB UNDIP</title>
    
    <!-- OpenGraph Meta Tags for WhatsApp & Social Media Preview -->
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $photo->title }} — FIB UNDIP">
    <meta property="og:description" content="{{ $photo->description ? Str::limit($photo->description, 150) : 'Dokumentasi kegiatan mahasiswa Sastra Jepang FIB Universitas Diponegoro.' }}">
    <meta property="og:image" content="{{ $photo->photo_url }}">
    <meta property="og:url" content="{{ url('/p/' . $photo->id) }}">
    <meta property="og:site_name" content="FIB UNDIP · 桜言葉">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Shippori+Mincho:wght@700&family=Noto+Serif+JP:wght@700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-washi: #FAF7F2;
            --bg-card: #FFFFFF;
            --border-paper: #E6DDD0;
            --text-sumi: #231F20;
            --text-charcoal: #4A4441;
            --text-muted: #7E7570;
            --sakura-coral: #E05668;
            --indigo-deep: #2A3B5C;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-washi);
            color: var(--text-sumi);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 760px;
            margin: 0 auto;
            padding: 20px 16px 60px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            margin-bottom: 24px;
            border-bottom: 1px solid var(--border-paper);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-sumi);
            font-family: 'Shippori Mincho', serif;
            font-weight: 800;
            font-size: 17px;
        }

        .btn-app {
            background: var(--sakura-coral);
            color: #FFFFFF;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
        }

        .photo-card {
            background: #FFFFFF;
            border: 1px solid var(--border-paper);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(50, 40, 30, 0.05);
            margin-bottom: 24px;
        }

        .uploader-header {
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #F3EDE3;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--sakura-coral);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 16px;
        }

        .uploader-name {
            font-weight: 700;
            font-size: 14px;
        }

        .uploader-sub {
            color: var(--text-muted);
            font-size: 11.5px;
        }

        .photo-wrapper img {
            width: 100%;
            max-height: 520px;
            object-fit: cover;
            display: block;
        }

        .photo-details {
            padding: 20px;
        }

        .photo-meta-bar {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 14px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .photo-title {
            font-family: 'Shippori Mincho', 'Noto Serif JP', serif;
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 10px;
            color: var(--text-sumi);
        }

        .photo-desc {
            color: var(--text-charcoal);
            font-size: 14.5px;
            line-height: 1.65;
            margin-bottom: 20px;
        }

        .share-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: var(--bg-washi);
            border-radius: 12px;
            border: 1px solid var(--border-paper);
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-wa {
            background: #25D366;
            color: #FFFFFF;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .comments-section {
            border-top: 1px solid var(--border-paper);
            padding-top: 20px;
        }

        .comments-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .comment-item {
            padding: 12px;
            background: var(--bg-washi);
            border-radius: 10px;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .comment-author {
            font-weight: 700;
            color: var(--text-sumi);
            margin-bottom: 2px;
        }

        .guest-cta {
            background: #FEF3C7;
            border: 1px solid #FDE68A;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            font-size: 13px;
            color: #92400E;
            margin-top: 16px;
        }

        .guest-cta a {
            color: var(--sakura-coral);
            font-weight: 700;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <a href="/" class="brand">
                <span>🌸 FIB UNDIP</span>
                <span style="color: var(--sakura-coral);">桜言葉</span>
            </a>
            <a href="/app/" class="btn-app">
                Buka Aplikasi &rarr;
            </a>
        </header>

        <div class="photo-card">
            <div class="uploader-header">
                <div class="avatar">
                    {{ strtoupper(substr($photo->user?->name ?? 'M', 0, 1)) }}
                </div>
                <div>
                    <div class="uploader-name">{{ $photo->user?->name ?? 'Mahasiswa FIB UNDIP' }}</div>
                    <div class="uploader-sub">
                        {{ $photo->location ?? 'FIB Universitas Diponegoro' }} · {{ $photo->event_date ?? $photo->created_at->format('d M Y') }}
                    </div>
                </div>
            </div>

            <div class="photo-wrapper">
                <img src="{{ $photo->photo_url }}" alt="{{ $photo->title }}">
            </div>

            <div class="photo-details">
                <div class="photo-meta-bar">
                    <span>❤️ <strong>{{ $photo->likes_count }}</strong> menyukai</span>
                    <span>💬 <strong>{{ $photo->comments_count }}</strong> komentar</span>
                </div>

                <h1 class="photo-title">{{ $photo->title }}</h1>

                @if($photo->description)
                    <p class="photo-desc">{{ $photo->description }}</p>
                @endif

                <div class="share-box">
                    <span style="font-size: 12.5px; font-weight: 600;">Bagikan momen ini:</span>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode($photo->title . ' - Dokumentasi FIB UNDIP: ' . url('/p/' . $photo->id)) }}" target="_blank" class="btn-wa">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        Share ke WhatsApp
                    </a>
                </div>

                <div class="comments-section">
                    <div class="comments-title">
                        <span>💬 Komentar Mahasiswa</span>
                        <span style="color: var(--text-muted); font-size: 13px;">({{ $photo->comments->count() }})</span>
                    </div>

                    @forelse($photo->comments as $c)
                        <div class="comment-item">
                            <div class="comment-author">{{ $c->user?->name ?? 'Mahasiswa' }}</div>
                            <div>{{ $c->comment }}</div>
                        </div>
                    @empty
                        <p style="color: var(--text-muted); font-size: 13px;">Belum ada komentar pada foto ini.</p>
                    @endforelse

                    <div class="guest-cta">
                        Ingin memberi <strong>Like ❤️</strong> atau menulis <strong>Komentar 💬</strong>? <br>
                        <a href="/app/">Buka Aplikasi Web</a> atau <a href="https://github.com/zusfan-ops/FIB-Backend/releases">Download APK Android</a> dan masuk dengan akun mahasiswa Anda.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
