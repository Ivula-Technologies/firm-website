/**
 * Everything about the firm in one place. Change details here and every page follows.
 * Phone, WhatsApp and address are placeholders until the firm confirms them.
 */
export const site = {
  brand: "JENIQA",
  brandAccent: "IQ",
  legalName: "JENIQA Advisory Limited",
  tagline: "Count what matters.",
  description:
    "Accounting, tax, advisory and ESG support for Kenyan businesses that want to grow, and to do it right.",
  url: (process.env.NEXT_PUBLIC_SITE_URL || "https://www.jeniqa.com").replace(/\/$/, ""),
  email: "info@jeniqa.com",
  phone: "+254 700 000 000",
  whatsapp: "254700000000",
  address: ["Upper Hill, Nairobi", "P.O. Box 00000-00100, Nairobi, Kenya"],
  mapQuery: "Upper Hill, Nairobi, Kenya",
  hours: ["Monday to Friday, 8:00am to 5:00pm", "Saturday by appointment"],
  social: {
    linkedin: "",
    x: "",
    facebook: "",
  },
} as const;

export const nav = [
  { href: "/services", label: "Services" },
  { href: "/industries", label: "Industries" },
  { href: "/insights", label: "Insights" },
  { href: "/free-tools", label: "Free tools" },
  { href: "/about", label: "About" },
  { href: "/contact", label: "Contact" },
];

export function whatsappUrl(text = `Hello ${site.brand}, I would like to ask about your services.`) {
  return `https://wa.me/${site.whatsapp}?text=${encodeURIComponent(text)}`;
}

export function telUrl() {
  return `tel:${site.phone.replace(/\s+/g, "")}`;
}

/** Rates for the free calculators (Finance Act position, February 2025). Update after each Finance Act. */
export const rates = {
  label: "February 2025",
  bands: [
    { upto: 24000, rate: 10 },
    { upto: 32333, rate: 25 },
    { upto: 500000, rate: 30 },
    { upto: 800000, rate: 32.5 },
    { upto: null, rate: 35 },
  ] as { upto: number | null; rate: number }[],
  relief: 2400,
  shif: 2.75,
  shifMin: 300,
  ahl: 1.5,
  nssf: 6,
  lel: 8000,
  uel: 72000,
  vat: 16,
};
