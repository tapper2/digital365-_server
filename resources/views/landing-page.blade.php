<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $page->title }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body { height: 100%; }

    body {
      font-family: 'Heebo', Arial, sans-serif;
      background: {{ $bg }};
      direction: rtl;
    }

    /* Row: 75% image | 25% form — fills full viewport height */
    .main-row {
      display: flex;
      height: 100vh;
      min-height: 520px;
    }

    /* col-image: hero image (75%) — full height, no bottom reservation */
    .col-image {
      flex: 0 0 75%;
      overflow: hidden;
      position: relative;
    }
    .col-image img {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: top center;
      display: block;
    }
    .col-image .hero-placeholder {
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    }

    /* form panel (25%) */
    .form-panel {
      flex: 0 0 25%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 32px 24px;
      background: #111827;
      border-right: 3px solid {{ $accent }};
      overflow-y: auto;
    }

    .form-header { margin-bottom: 24px; text-align: center; }
    .form-header h2 { font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 4px; }
    .form-divider { display: flex; align-items: center; gap: 8px; margin: 8px 0; }
    .form-divider-line { height: 1px; flex: 1; background: {{ $accent }}; opacity: 0.4; }
    .form-divider-diamond { color: {{ $accent }}; font-size: 10px; }
    .form-header p { font-size: 14px; color: #9ca3af; }

    form { display: flex; flex-direction: column; gap: 12px; }

    .field-wrapper { position: relative; }

    input, textarea {
      width: 100%;
      background: transparent;
      color: #fff;
      font-size: 14px;
      font-family: 'Heebo', Arial, sans-serif;
      padding: 10px 16px;
      outline: none;
      border: 1px solid rgba(255,255,255,0.12);
      border-bottom: 2px solid {{ $accent }}4d;
      border-radius: 6px;
    }
    input::placeholder, textarea::placeholder { color: #6b7280; }
    textarea { resize: none; border-bottom: 2px solid {{ $accent }}33; }

    .submit-btn {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 12px;
      margin-top: 4px;
      font-family: 'Heebo', Arial, sans-serif;
      font-size: 14px;
      font-weight: 700;
      color: #fff;
      background: {{ $accent }};
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: opacity 0.15s;
    }
    .submit-btn:hover { opacity: 0.9; }

    .privacy-note { font-size: 12px; color: #6b7280; text-align: center; margin-top: 12px; }

    /* Contact icons — inside form panel */
    .contact-links {
      margin-top: 20px;
      padding-top: 18px;
      border-top: 1px solid rgba(255,255,255,0.08);
    }
    .contact-links-label {
      font-size: 11px;
      color: #4b5563;
      text-align: center;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      margin-bottom: 14px;
    }
    .contact-icons-row {
      display: flex;
      justify-content: center;
      gap: 14px;
      flex-wrap: wrap;
    }
    .contact-icon-link {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 5px;
      text-decoration: none;
      color: inherit;
      cursor: pointer;
    }
    .contact-icon-btn {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1.5px solid {{ $accent }}80;
      color: {{ $accent }};
      background: {{ $accent }}0d;
      transition: all 0.18s;
    }
    .contact-icon-link:hover .contact-icon-btn {
      border-color: {{ $accent }};
      background: {{ $accent }}22;
    }
    .contact-icon-label {
      font-size: 10px;
      color: #6b7280;
      white-space: nowrap;
    }

    /* Success message */
    .success-msg {
      display: none;
      background: #052e16;
      border: 1px solid #16a34a;
      border-radius: 8px;
      padding: 16px;
      color: #4ade80;
      text-align: center;
      font-size: 15px;
      font-weight: 600;
      margin-top: 8px;
    }
  </style>
</head>
<body>

<div class="main-row">

  <!-- hero image (75%) — full viewport height -->
  <div class="col-image">
    @if($heroImageUrl)
      <img src="{{ $heroImageUrl }}" alt="{{ $page->title }}" />
    @else
      <div class="hero-placeholder"></div>
    @endif
  </div>

  <!-- form panel (25%) -->
  <div class="form-panel">
    @if($logoUrl ?? null)
    <div style="display:flex;justify-content:center;margin-bottom:20px">
      <img src="{{ $logoUrl }}" alt="logo" style="width:60%;height:64px;object-fit:contain;display:block" />
    </div>
    @endif

    <div class="form-header">
      <h2>לקבלת ייעוץ מקצועי</h2>
      <div class="form-divider">
        <div class="form-divider-line"></div>
        <span class="form-divider-diamond">◆</span>
      </div>
      <p>השאירו פרטים ונחזור אליכם</p>
    </div>

    <form id="contact-form">
      <div class="field-wrapper">
        <input type="text" name="name" placeholder="שם מלא" required />
      </div>
      <div class="field-wrapper">
        <input type="tel" name="phone" placeholder="טלפון" required style="text-align:right;direction:rtl" />
      </div>
      <div class="field-wrapper">
        <input type="email" name="email" placeholder="אימייל" />
      </div>
      <div class="field-wrapper">
        <input type="text" name="business_type" placeholder="תחום עיסוק" />
      </div>
      <div class="field-wrapper">
        <textarea name="notes" placeholder="פרטים נוספים" rows="3"></textarea>
      </div>

      <button type="submit" class="submit-btn">
        <span>שלחו פרטים</span>
        <span style="font-size:16px">‹</span>
      </button>
    </form>

    <div class="success-msg" id="success-msg">
      ✅ תודה! נחזור אליכם בקרוב.
    </div>

    <p class="privacy-note">🔒 הפרטים שלכם בטוחים ומאובטחים</p>

    {{-- Contact icons below the form --}}
    @php
      $hasPhone     = !empty($settings['phone']);
      $hasWhatsapp  = !empty($settings['whatsapp']);
      $hasInstagram = !empty($settings['instagram_url']);
      $hasFacebook  = !empty($settings['facebook_url']);
      $hasEmail     = !empty($settings['email_contact']);
      $hasAnyContact = $hasPhone || $hasWhatsapp || $hasInstagram || $hasFacebook || $hasEmail;
    @endphp

    <div class="contact-links">
      <p class="contact-links-label">צור קשר</p>
      <div class="contact-icons-row">

        {{-- Phone --}}
        @if($hasPhone)
        <a href="tel:{{ $settings['phone'] }}" class="contact-icon-link" title="חייגו">
        @else
        <span class="contact-icon-link" style="opacity:0.3;cursor:default">
        @endif
          <div class="contact-icon-btn">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.61 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.09 6.09l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
          </div>
          <span class="contact-icon-label">חייגו</span>
        @if($hasPhone)</a>@else</span>@endif

        {{-- WhatsApp --}}
        @if($hasWhatsapp)
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['whatsapp']) }}" target="_blank" rel="noreferrer" class="contact-icon-link" title="וואטסאפ">
        @else
        <span class="contact-icon-link" style="opacity:0.3;cursor:default">
        @endif
          <div class="contact-icon-btn">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
            </svg>
          </div>
          <span class="contact-icon-label">וואטסאפ</span>
        @if($hasWhatsapp)</a>@else</span>@endif

        {{-- Instagram --}}
        @if($hasInstagram)
        <a href="{{ $settings['instagram_url'] }}" target="_blank" rel="noreferrer" class="contact-icon-link" title="אינסטגרם">
        @else
        <span class="contact-icon-link" style="opacity:0.3;cursor:default">
        @endif
          <div class="contact-icon-btn">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
              <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
              <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
            </svg>
          </div>
          <span class="contact-icon-label">אינסטגרם</span>
        @if($hasInstagram)</a>@else</span>@endif

        {{-- Facebook --}}
        @if($hasFacebook)
        <a href="{{ $settings['facebook_url'] }}" target="_blank" rel="noreferrer" class="contact-icon-link" title="פייסבוק">
        @else
        <span class="contact-icon-link" style="opacity:0.3;cursor:default">
        @endif
          <div class="contact-icon-btn">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
          </div>
          <span class="contact-icon-label">פייסבוק</span>
        @if($hasFacebook)</a>@else</span>@endif

        {{-- Email --}}
        @if($hasEmail)
        <a href="mailto:{{ $settings['email_contact'] }}" class="contact-icon-link" title="אימייל">
        @else
        <span class="contact-icon-link" style="opacity:0.3;cursor:default">
        @endif
          <div class="contact-icon-btn">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
          </div>
          <span class="contact-icon-label">אימייל</span>
        @if($hasEmail)</a>@else</span>@endif

      </div>
    </div>

  </div><!-- /form-panel -->
</div><!-- /main-row -->

<script>
  document.getElementById('contact-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('.submit-btn');
    btn.disabled = true;
    btn.textContent = 'שולח...';

    const data = {};
    new FormData(form).forEach(function(v, k) { data[k] = v; });

    try {
      const res = await fetch('{{ config('app.url') }}/api/submit/{{ $page->token }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(data),
      });

      if (res.ok) {
        form.style.display = 'none';
        document.getElementById('success-msg').style.display = 'block';
      } else {
        btn.disabled = false;
        btn.innerHTML = '<span>שלחו פרטים</span><span style="font-size:16px">‹</span>';
        alert('שגיאה בשליחה, נסו שוב.');
      }
    } catch(err) {
      btn.disabled = false;
      btn.innerHTML = '<span>שלחו פרטים</span><span style="font-size:16px">‹</span>';
      alert('שגיאה בחיבור לשרת.');
    }
  });
</script>
</body>
</html>
