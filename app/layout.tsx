import type { Metadata, Viewport } from "next";
import localFont from "next/font/local";
import "./globals.css";
import { Header } from "@/components/Header";
import { Footer } from "@/components/Footer";
import { RevealObserver } from "@/components/Reveal";
import { services } from "@/lib/content";
import { site } from "@/lib/site";

const display = localFont({ src: "./fonts/manrope-latin-wght-normal.woff2", variable: "--font-display", weight: "200 800", display: "swap" });
const body = localFont({ src: "./fonts/inter-latin-wght-normal.woff2", variable: "--font-body", weight: "100 900", display: "swap" });

export const metadata: Metadata = {
  metadataBase: new URL(site.url),
  title: { default: `${site.brand} | Accounting, tax, advisory and ESG in Nairobi`, template: `%s | ${site.brand}` },
  description: site.description,
  applicationName: site.brand,
  openGraph: { type: "website", siteName: site.brand, locale: "en_KE" },
  twitter: { card: "summary_large_image" },
  alternates: { canonical: "/" },
};

export const viewport: Viewport = { themeColor: "#0A1B33" };

const org = {
  "@context": "https://schema.org",
  "@type": "AccountingService",
  "@id": `${site.url}/#organization`,
  name: site.brand,
  legalName: site.legalName,
  slogan: site.tagline,
  description: site.description,
  url: site.url,
  logo: `${site.url}/icon.svg`,
  image: `${site.url}/opengraph-image.png`,
  email: site.email,
  telephone: site.phone,
  areaServed: { "@type": "Country", name: "Kenya" },
  address: { "@type": "PostalAddress", streetAddress: site.address[0], addressLocality: "Nairobi", addressCountry: "KE" },
  knowsAbout: services.map((s) => s.title),
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en-KE" className={`${display.variable} ${body.variable}`}>
      <body>
        <a className="skip" href="#main">Skip to content</a>
        <Header />
        <main id="main">{children}</main>
        <Footer />
        <RevealObserver />
        <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(org) }} />
      </body>
    </html>
  );
}
