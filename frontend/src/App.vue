<template>
  <div class="layout">

    <header class="site-header">
      <div class="header-inner">
        <div class="brand">
          <span class="brand-name">LifeHub</span>
          <span class="brand-slogan">Dein Leben. Organisiert. Verbunden.</span>
        </div>
        <slot name="nav" />
      </div>
    </header>

    <main class="site-main">
      <RouterView />
    </main>

    <footer class="site-footer">
      <div class="footer-inner">
        <div class="footer-brand">
          <span class="footer-logo">LifeHub</span>
          <span class="footer-copy">© 2026 LifeHub. Alle Rechte vorbehalten.</span>
        </div>
        <nav class="footer-nav">
          <a href="/impressum">Impressum</a>
          <a href="/datenschutz">Datenschutz</a>
          <a href="/agb">AGB</a>
          <a href="/kontakt">Kontakt</a>
        </nav>
      </div>
    </footer>

  </div>
</template>

<script setup>
import { RouterView } from 'vue-router'
</script>

<style>
*, *::before, *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: system-ui, -apple-system, sans-serif;
  background: #f7f8fa;
  color: #1a1a2e;
}
</style>

<style scoped>
.layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ── Header ── */
.site-header {
  background: #1a1a2e;
  border-bottom: 1px solid rgba(255,255,255,0.06);
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.brand {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.brand-name {
  font-size: 1.4rem;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.5px;
}

.brand-slogan {
  font-size: 0.7rem;
  color: rgba(255,255,255,0.45);
  letter-spacing: 0.3px;
}

/* ── Main ── */
.site-main {
  flex: 1;
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
  padding: 2.5rem 2rem;
}

/* ── Footer ── */
.site-footer {
  background: #1a1a2e;
  border-top: 1px solid rgba(255,255,255,0.06);
  margin-top: auto;
}

.footer-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}

.footer-brand {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.footer-logo {
  font-size: 1rem;
  font-weight: 700;
  color: #fff;
}

.footer-copy {
  font-size: 0.75rem;
  color: rgba(255,255,255,0.35);
}

.footer-nav {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.footer-nav a {
  font-size: 0.8rem;
  color: rgba(255,255,255,0.5);
  text-decoration: none;
  transition: color 0.2s;
}

.footer-nav a:hover {
  color: #fff;
}

/* ── Responsive ── */
@media (max-width: 600px) {
  .header-inner {
    padding: 0 1rem;
    height: 60px;
  }

  .site-main {
    padding: 1.5rem 1rem;
  }

  .footer-inner {
    flex-direction: column;
    align-items: flex-start;
    padding: 1.25rem 1rem;
  }

  .footer-nav {
    gap: 1rem;
  }
}
</style>
