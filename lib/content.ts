import raw from "@/data/content.json";

export type Group = "money" | "obligations" | "impact";
export type Service = {
  slug: string;
  title: string;
  group: Group;
  icon: string;
  promise: string;
  who: string;
  seo: string;
  subs: { title: string; text: string; assurance?: boolean }[];
  steps: { title: string; text: string }[];
  faqs: { q: string; a: string }[];
};
export type Industry = { title: string; icon: string; summary: string; services: string[] };
export type Post = {
  slug: string;
  date: string;
  title: string;
  excerpt: string;
  html: string;
  cats: string[];
  topics: string[];
  services: string[];
  meta: Record<string, string>;
};

export const services = raw.services as Service[];
export const industries = raw.industries as Industry[];
export const promises = raw.promises as { icon: string; title: string; text: string }[];
export const posts = (raw.posts as unknown as Post[]).slice().sort((a, b) => b.date.localeCompare(a.date));
export const glossary = raw.glossary as { term: string; def: string }[];
export const legal = raw.legal as { privacy: string; cookies: string; terms: string };

export const groups: Record<Group, { label: string; icon: string; line: string }> = {
  money: { label: "Your money", icon: "coins", line: "What comes in, what goes out, and who gets paid." },
  obligations: { label: "Your obligations", icon: "scale", line: "What the law, KRA and your stakeholders expect of you." },
  impact: { label: "Your impact", icon: "sprout", line: "Where the business is going, and what it leaves behind." },
};

export const categories: Record<string, string> = {
  "tax-alerts": "Tax alerts",
  "case-analysis": "Case analysis",
  "esg-reporting": "ESG reporting",
  guides: "Guides",
  "news-events": "News and events",
};

export const downloads = [
  {
    title: "ESG readiness checklist",
    kind: "Checklist",
    text: "Twenty questions to see where your organisation stands before you start sustainability reporting.",
    file: "/downloads/esg-readiness-checklist.pdf",
  },
  {
    title: "Materiality assessment worksheet",
    kind: "Template",
    text: "A worksheet to list, score and prioritise sustainability topics with your team.",
    file: "/downloads/materiality-assessment-worksheet.pdf",
  },
  {
    title: "Kenya tax compliance calendar",
    kind: "Publication",
    text: "Every monthly and annual tax and statutory deadline for a Kenyan business, on one page.",
    file: "/downloads/kenya-tax-compliance-calendar.pdf",
  },
];

export function getService(slug: string) {
  return services.find((s) => s.slug === slug);
}
export function getPost(slug: string) {
  return posts.find((p) => p.slug === slug);
}
export function primaryCategory(p: Post) {
  if (p.cats.includes("case-analysis")) return "case-analysis";
  return p.cats[0] || "news-events";
}
export function formatDate(iso: string) {
  const [y, m, d] = iso.split("-").map(Number);
  const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
  return `${d} ${months[m - 1]} ${y}`;
}
export function readingTime(html: string) {
  const words = html.replace(/<[^>]+>/g, " ").split(/\s+/).filter(Boolean).length;
  return Math.max(1, Math.round(words / 200));
}

/** Tiny Markdown for the legal pages: ## headings, - lists and paragraphs. */
export function mdToHtml(md: string) {
  const esc = (s: string) => s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
  return md
    .trim()
    .split(/\n{2,}/)
    .map((block) => {
      if (block.startsWith("## ")) return `<h2>${esc(block.slice(3))}</h2>`;
      const lines = block.split("\n");
      if (lines.every((l) => l.startsWith("- "))) return `<ul>${lines.map((l) => `<li>${esc(l.slice(2))}</li>`).join("")}</ul>`;
      return `<p>${esc(block)}</p>`;
    })
    .join("\n");
}
