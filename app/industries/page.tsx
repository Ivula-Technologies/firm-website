import type { Metadata } from "next";
import Link from "next/link";
import { Icon } from "@/components/Icon";
import { Cta, PageHero } from "@/components/ui";
import { getService, industries } from "@/lib/content";

export const metadata: Metadata = {
  title: "Industries",
  description: "How JENIQA helps SMEs, NGOs, SACCOs, real estate, professional firms, schools, hospitality and agribusiness in Kenya.",
  alternates: { canonical: "/industries" },
};

const id = (t: string) => t.toLowerCase().replace(/[^a-z0-9]+/g, "-");

export default function IndustriesPage() {
  return (
    <>
      <PageHero eyebrow="Industries" title="We work across the Kenyan economy" text="Every sector has its own rules, risks and reporting. Here is how we help in each.">
        <nav className="chipnav" aria-label="Industries on this page">
          {industries.map((i) => <a key={i.title} href={`#${id(i.title)}`}><Icon name={i.icon} size={16} /> {i.title}</a>)}
        </nav>
      </PageHero>
      <section className="section section--soft">
        <div className="container ind-list">
          {industries.map((ind) => (
            <article key={ind.title} id={id(ind.title)} className="ind reveal">
              <span className="ind__icon"><Icon name={ind.icon} size={26} /></span>
              <div>
                <h2>{ind.title}</h2>
                <p>{ind.summary}</p>
                <ul className="pills" aria-label="Services that help">
                  {ind.services.map((slug) => {
                    const s = getService(slug);
                    return s ? <li key={slug}><Link href={`/services/${slug}`}>{s.title}</Link></li> : null;
                  })}
                </ul>
              </div>
              <Link href="/request-a-proposal" className="btn btn--ghost btn--sm">Talk to us</Link>
            </article>
          ))}
        </div>
      </section>
      <Cta />
    </>
  );
}
