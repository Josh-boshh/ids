<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Case studies | I.D.S Admin</title>
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <link rel="apple-touch-icon" href="../assets/img/favicon.png">
  <link rel="stylesheet" href="admin.css">
  <script src="admin.js?v=20261003" defer></script>
</head>
<body class="admin-page">
  <header class="admin-header">
    <a class="site-link back-link" href="../index.html"><span aria-hidden="true">←</span> Back to site</a>
  </header>

  <main>
    <section id="auth-view" class="auth-view" aria-labelledby="auth-title" hidden>
      <div class="auth-copy"><p class="eyebrow">I.D.S / Private workspace</p><h1 id="auth-title">Case studies, kept in order.</h1><p id="auth-message">Sign in to manage the portfolio.</p></div>
      <form id="auth-form" class="auth-form" autocomplete="on">
        <div class="auth-form-heading"><p class="eyebrow">Welcome back</p><h2>Admin sign in</h2></div>
        <label class="auth-field" for="auth-password"><span id="password-label">Password</span><input id="auth-password" name="password" type="password" autocomplete="current-password" required placeholder="Enter your password"></label>
        <button class="button button-primary auth-submit" type="submit"><span>Sign in</span><span aria-hidden="true">↗</span></button>
        <p id="auth-error" class="form-error" role="alert" aria-live="polite"></p>
      </form>
    </section>

    <section id="admin-home" class="admin-home" aria-labelledby="admin-home-title" hidden>
      <div class="admin-home-heading"><div><p class="eyebrow">I.D.S / Admin</p><h1 id="admin-home-title">What would you like to manage?</h1></div><button id="logout-button" class="icon-button" type="button" hidden>Sign out</button></div>
      <div class="admin-home-options">
        <button id="open-cases-button" class="admin-home-option" type="button"><span class="admin-home-icon" aria-hidden="true">▤</span><span><strong>Case studies</strong><small>Create, edit, and publish project stories</small></span><span class="admin-home-arrow" aria-hidden="true">↗</span></button>
        <button id="open-site-seo-button" class="admin-home-option" type="button"><span class="admin-home-icon" aria-hidden="true">⌕</span><span><strong>Site SEO</strong><small>Manage I.D.S Integrated search and social previews</small></span><span class="admin-home-arrow" aria-hidden="true">↗</span></button>
      </div>
    </section>

    <section id="workspace" class="workspace" hidden>
      <aside class="case-sidebar" aria-label="Case studies list">
        <nav class="sidebar-nav" aria-label="Admin sections">
          <button id="admin-home-nav-button" class="sidebar-nav-button sidebar-home-button" type="button"><span aria-hidden="true">←</span> Admin home</button>
          <button id="cases-nav-button" class="sidebar-nav-button is-active" type="button" aria-pressed="true"><span aria-hidden="true">▤</span> Case studies</button>
          <button id="site-seo-nav-button" class="sidebar-nav-button" type="button" aria-pressed="false"><span aria-hidden="true">⌕</span> Site SEO</button>
        </nav>
        <div id="case-collection">
          <div class="sidebar-heading"><div><p class="eyebrow">Portfolio / <span id="case-count">03 entries</span></p><h1>Case studies</h1></div><button id="new-case-button" class="button button-dark" type="button"><span aria-hidden="true">+</span> New</button></div>
          <label class="search-box"><span class="sr-only">Search case studies</span><input id="case-search" type="search" placeholder="Find a case study" autocomplete="off"><span aria-hidden="true">⌕</span></label>
          <div id="case-list" class="case-list" aria-live="polite"></div>
          <p id="empty-list" class="empty-list" hidden>No case studies yet. Start with a new entry.</p>
        </div>
      </aside>

      <section id="case-editor" class="editor" aria-labelledby="editor-title">
        <div class="editor-head"><div><p class="eyebrow">Editor</p><h2 id="editor-title">New case study</h2></div><a id="preview-link" class="preview-link" href="#" target="_blank" rel="noopener" hidden>Open preview <span aria-hidden="true">↗</span></a></div>
        <form id="case-form">
          <input type="hidden" name="id">
          <div class="field-grid">
            <label class="field field-wide"><span>Project title <b>*</b></span><input name="title" maxlength="120" required placeholder="Project or client name"></label>
            <label class="field"><span>URL slug <b>*</b></span><input name="slug" maxlength="80" pattern="[a-z0-9]+(-[a-z0-9]+)*" required placeholder="project-name"><small>Lowercase letters, numbers and hyphens</small></label>
            <label class="field"><span>Category <b>*</b></span><input name="category" maxlength="100" required placeholder="Government &amp; public sector"></label>
            <label class="field field-wide"><span>Card summary <b>*</b></span><textarea name="summary" rows="3" maxlength="280" required placeholder="A concise introduction for the homepage card"></textarea></label>
            <label class="field"><span>Cover image path or HTTPS URL <b>*</b></span><input name="image" required placeholder="assets/img/case-studies/project.jpg"></label>
            <label class="field"><span>Image description (alt text) <b>*</b></span><input name="imageAlt" maxlength="180" required placeholder="Describe what is visible in the image"></label>
            <label class="field field-wide"><span>Case study content <b>*</b></span><textarea name="content" rows="8" required placeholder="Write the case study. Separate paragraphs with a blank line."></textarea></label>
          </div>

          <div class="publish-row">
            <label class="check-control"><input name="published" type="checkbox"><span class="check-box" aria-hidden="true"></span><span><strong>Publish on site</strong><small>Visible to site visitors when enabled</small></span></label>
            <label class="check-control"><input name="featured" type="checkbox"><span class="check-box" aria-hidden="true"></span><span><strong>Feature on homepage</strong><small>Show in the lead position</small></span></label>
          </div>
          <div class="form-footer"><div class="footer-actions"><button class="button button-primary" type="submit"><span id="save-label">Save changes</span><span aria-hidden="true">↗</span></button><button id="delete-button" class="button button-danger" type="button" hidden>Delete</button></div><p id="save-status" role="status" aria-live="polite"></p></div>
        </form>
      </section>

      <section id="site-seo-editor" class="editor site-seo-editor" aria-labelledby="site-seo-heading" hidden>
        <div class="editor-head"><div><p class="eyebrow">Website settings</p><h2 id="site-seo-heading">Site SEO</h2></div><a class="preview-link" href="../index.html" target="_blank" rel="noopener">View homepage <span aria-hidden="true">↗</span></a></div>
        <form id="site-seo-form">
          <section class="seo-section site-seo-section" aria-labelledby="site-search-heading">
            <div class="section-heading"><div><p class="eyebrow">Search engines</p><h3 id="site-search-heading">Search appearance</h3></div></div>
            <div class="field-grid">
              <label class="field"><span>Site or brand name <b>*</b></span><input name="siteName" maxlength="120" required placeholder="I.D.S Integrated"></label>
              <label class="field"><span>Canonical homepage URL</span><input name="canonicalUrl" type="url" maxlength="2048" placeholder="https://example.com/"></label>
              <label class="field field-wide"><span>SEO title <b>*</b></span><input name="seoTitle" maxlength="120" required><small><span id="site-seo-title-count">0</span> / 120 characters</small></label>
              <label class="field field-wide"><span>Meta description <b>*</b></span><textarea name="metaDescription" rows="4" maxlength="320" required></textarea><small><span id="site-meta-description-count">0</span> / 320 characters</small></label>
            </div>
          </section>

          <section class="social-section site-social-section" aria-labelledby="site-social-heading">
            <div class="section-heading"><div><p class="eyebrow">Link previews</p><h3 id="site-social-heading">Social sharing</h3></div></div>
              <div class="field-grid">
                <label class="field field-wide"><span>Social preview title</span><input name="ogTitle" maxlength="120" placeholder="Defaults to the SEO title"></label>
                <label class="field field-wide"><span>Social preview description</span><textarea name="ogDescription" rows="3" maxlength="320" placeholder="Defaults to the meta description"></textarea></label>
                <label class="field field-wide"><span>Social preview image URL or site path</span><input name="ogImage" maxlength="2048" placeholder="assets/img/ids-logo.png"></label>
              </div>
              <div class="search-preview site-search-preview"><span class="preview-label">Search preview</span><p id="site-preview-title">Your site title appears here</p><small id="site-preview-url">idsintegrated.com</small><p id="site-preview-description">Your site description appears here.</p></div>
          </section>

          <div class="form-footer"><div class="footer-actions"><button class="button button-primary" type="submit"><span>Save site SEO</span><span aria-hidden="true">↗</span></button></div><p id="site-seo-status" role="status" aria-live="polite"></p></div>
        </form>
      </section>
    </section>
  </main>
  <div id="toast" class="toast" role="status" aria-live="polite"></div>
</body>
</html>
