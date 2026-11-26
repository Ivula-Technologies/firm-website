import type { Metadata } from "next";
import Link from "next/link";
import { Icon } from "@/components/Icon";
import { Cta, PageHero, PostCard, SectionHead } from "@/components/ui";
import { downloads, glossary, posts } from "@/lib/content";

export const metadata: Metadata = {
  title: "ESG reporting hub",
  description: "Practical guidance on sustainability reporting for Kenyan organisations: a step-by-step series, a glossary and free checklists.",
  alternates: { canonical: "/esg-reporting-hub" },
};

export default function HubPage() {
  const series = posts.filter((p) => p.meta.series_part).sort((a, b) => +a.meta.series_part - +b.meta.series_part);
  const esg = posts.filter((p) => p.cats.includes("esg-reporting") && !p.meta.series_part);
  return (
    <>
      <PageHero eyebrow="ESG reporting hub" title="Sustainability reporting, made practical" text="ICPAK has adopted the IFRS sustainability standards, the NSE has ESG disclosure guidance, and lenders, investors and export buyers now ask for ESG information as a matter of course. Start here.">
        <nav className="chipnav" aria-label="On this page">
          <a href="#series"><Icon name="book-open" size={16} /> Start here</a>
          <a href="#downloads"><Icon name="download" size={16} /> Free downloads</a>
          <a href="#glossary"><Icon name="info" size={16} /> Glossary</a>
          <Link href="/free-tools#esg-check"><Icon name="leaf" size={16} /> ESG readiness check</Link>
        </nav>
      </PageHero>

      <section className="section" id="series">
        <div className="container">
          <SectionHead eyebrow="Start here" title="ESG reporting for Kenyan businesses" text="A step-by-step series for organisations preparing their first sustainability report. Read it in order." />
          <ol className="series">
            {series.map((p) => (
              <li key={p.slug} className="reveal">
                <Link href={`/insights/${p.slug}`}>
                  <strong>{p.title}</strong>
                  <Icon name="arrow-right" size={18} />
                </Link>
              </li>
            ))}
          </ol>
        </div>
      </section>

      {esg.length > 0 && (
        <section className="section section--soft">
          <div className="container">
            <SectionHead eyebrow="More ESG reading" title="From our ESG team" />
            <div className="posts">{esg.map((p) => <PostCard key={p.slug} post={p} />)}</div>
          </div>
        </section>
      )}

      <section className="section" id="downloads">
        <div className="container">
          <SectionHead eyebrow="Free downloads" title="Checklists and templates to use with your team" />
          <div className="dl-grid">
            {downloads.map((d) => (
              <div key={d.title} className="dl reveal">
                <small>{d.kind}</small>
                <h3>{d.title}</h3>
                <p>{d.text}</p>
                <a className="btn btn--ghost btn--sm" href={d.file} download><Icon name="download" size={16} /> Download PDF</a>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="section section--soft" id="glossary">
        <div className="container">
          <SectionHead eyebrow="Glossary" title="ESG terms, in plain language" />
          <dl className="glossary">
            {glossary.map((g) => (
              <div key={g.term} className="reveal"><dt>{g.term}</dt><dd>{g.def}</dd></div>
            ))}
          </dl>
        </div>
      </section>
      <Cta title="Preparing your first ESG report?" text="We will help you decide what to report, measure it properly and publish something you can stand behind." />
    </>
  );
}
