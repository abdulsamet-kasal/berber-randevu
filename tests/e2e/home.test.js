import { test, expect } from '@playwright/test';

test.describe('Sayfa yapisi', () => {
  test('ana sayfa basligi ve tema dogru', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/Berber Randevu/);
    // Yeni koyu tema: body arka plani ink rengi
    const bg = await page.evaluate(() => getComputedStyle(document.body).backgroundColor);
    expect(bg).toBe('rgb(20, 17, 14)'); // #14110E
  });

  test('hizmetler sayfasina navigasyon', async ({ page }) => {
    await page.goto('/');
    await page.click('nav a[href="/hizmetler"]');
    await page.waitForURL('**/hizmetler');
    await expect(page.locator('h2').first()).toContainText('Hizmetlerimiz');
  });

  test('randevu sayfasina navigasyon ve form', async ({ page }) => {
    await page.goto('/');
    await page.click('header a.btn-primary[href="/randevu"]');
    await page.waitForURL('**/randevu');
    await expect(page.locator('#appointmentForm')).toBeVisible();
    await expect(page.locator('#staffSelect')).toBeVisible();
    await expect(page.locator('#dateSelect')).toBeVisible();
  });

  test('tum sayfalar 200 donuyor ve render ediliyor', async ({ page }) => {
    for (const path of ['/', '/hizmetler', '/ekip', '/galeri', '/yorumlar', '/iletisim', '/randevu', '/randevu-sorgula', '/admin/giris']) {
      const response = await page.goto(path);
      expect(response.status(), path + ' 200 olmali').toBe(200);
      await expect(page.locator('footer')).toBeVisible();
    }
  });
});

test.describe('Admin akisi', () => {
  test('token yokken /admin girise yonlendirir', async ({ page }) => {
    await page.goto('/admin');
    await page.waitForURL('**/admin/giris');
  });

  test('giris formu elementleri mevcut', async ({ page }) => {
    await page.goto('/admin/giris');
    await expect(page.locator('#adminEmail')).toBeVisible();
    await expect(page.locator('#adminPassword')).toBeVisible();
    await expect(page.locator('#adminLoginForm button[type=submit]')).toBeVisible();
  });
});

test.describe('Randevu formu dinamigi', () => {
  test('hizmet yukleme durumu gosterilir', async ({ page }) => {
    await page.goto('/randevu');
    // API ya hizmetleri yukler ya da hata metni gosterir - ikisi de ok
    await expect(page.locator('#serviceList')).not.toBeEmpty();
  });
});
