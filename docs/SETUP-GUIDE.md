# Firm website: setup on cPanel

The site is a WordPress theme (`cwm-theme.zip`) that sets itself up. Activating it creates every page, the 7 services, the 8 industries, the menus, the ESG hub, the glossary, sample insights and the forms. No plugins are required apart from one for sending email.

## 0. Show it to the client first (staging)
Put the site on a staging address on Ivula Hosting before it goes anywhere near the client's domain.
1. In the ivulahosting.co.ke cPanel, go to **Domains > Create a new domain** and add a subdomain such as `firm.ivulahosting.co.ke`. Its folder will be `public_html/firm`.
2. Run **SSL/TLS Status > Run AutoSSL** so the subdomain gets https.
3. Do steps 1 and 2 below on that subdomain.
4. **Preview mode is on from the start.** Search engines are kept out (noindex, robots.txt, no sitemap), every page shows a slim "Preview" banner, and test form emails arrive marked "[Preview]".
5. Go to **Firm settings > Preview and launch** and fill in:
   - **Preview password**, e.g. a word plus the year. The client types it once and their device remembers it for 30 days. You never see the gate while you're logged in.
   - **Feedback goes to**: your WhatsApp number or email. The banner then gets a "Send feedback on this page" link that already includes the page address, so the client's comments arrive saying which page they mean.
6. Send the client the link and the password. Walk them through it page by page, following the sign-off order at the bottom of this guide.
7. **After the final payment**, move the site to the client's domain. Use Softaculous **Clone** if their domain is in the same cPanel account. Otherwise use the free **All-in-One WP Migration** plugin: export on staging, import on the client's site. Then untick Preview mode (see Go live below) and delete the staging copy.

**Updating the theme later:** Appearance > Themes > Add New > Upload, pick the new zip, then choose **Replace current with uploaded**. Content and settings stay as they are.

## 1. Install WordPress (5 minutes)
1. cPanel > **Softaculous** (or "WordPress Manager") > Install. Choose the client's domain and leave the directory empty.
2. Use a strong admin password, and an admin email that the firm controls.

## 2. Install the theme
1. WP Admin > **Appearance > Themes > Add New > Upload Theme**, then pick `cwm-theme.zip` and click **Activate**.
2. Setup runs once, by itself. It creates the pages, the content and the menus, sets permalinks to `/insights/...`, and sets the timezone to Nairobi.
3. Go to **Firm settings > Launch checklist**. It lists everything still open.

## 3. Fill in Firm settings (Firm settings > Firm details)
- Firm name (it starts as "Firm Name" on purpose), tagline, phone, email, WhatsApp, address and map location.
- Logo (optional; until one is uploaded, a built-in mark and the firm name are used).
- Firm profile PDF. Download buttons stay hidden until it is uploaded.
- **Forms tab**: the shared inbox that every form goes to (never a person's email), and the optional auto-reply.

## 4. Email that actually arrives
Install **WP Mail SMTP** or **FluentSMTP**, then connect it to the firm's cPanel mailbox (SMTP host = mail.domain, port 465). Every submission is also saved under **Enquiries**, where it can be exported to CSV, so nothing is lost if email fails.

## 5. Go live
1. Firm settings > Preview and launch: **untick Preview mode**. This removes the banner and the password, and lets search engines in.
2. cPanel > **SSL/TLS Status > Run AutoSSL**. Then, in Settings > General, change both addresses to `https://`.
3. Settings > Reading: untick **"Discourage search engines"**.
4. Launch checklist > **Delete sample content**, once the firm's own articles are in. The 12 sample insights and 3 sample PDFs are there only so every layout can be seen.
5. Optional: add the GA4 ID in Firm settings. Analytics loads only after a visitor accepts cookies.
6. Don't install Yoast or Rank Math unless the client asks. The theme already handles titles, descriptions, sitemaps, social cards and Google structured data. If one of those plugins is installed, the theme's own SEO steps aside automatically.

## The two switches (Firm settings > Switches)
- **Switch A, Audit**:
  - **Hidden** (the launch default): the word "audit" appears nowhere and the audit page returns "not found".
  - **Partner**: the audit page appears, saying it is carried out by the named partner firm, licensed by ICPAK.
  - **Live**: the firm's own audit service.
- **Switch B, Team**: off by default, so no names, photos or authors appear anywhere and articles are signed by the team ("Tax Team"). When it is on, only profiles ticked "Published" show.

## What to get from the client
- Final firm name, the logo decision (theirs, or ours designed as an extra), phone, email, WhatsApp and office address.
- Firm profile PDF and any service PDFs.
- For Switch A Partner mode: the partner audit firm's name and licence line.
- Real statistics for the homepage (the block is hidden until numbers are entered), testimonials (with permission) and their first real articles or tax alerts.

## Sign-off
The brief asks for step-by-step sign-off. The build covers every step, so walk the client through it in this order: home, services, industries, insights (including tax case analyses), ESG hub, about/approach, contact/proposal, legal pages, then the switches. The screenshots in `screenshots/` follow the same order.
