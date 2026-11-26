import { Photo } from "@/components/Photo";
import { servicePhoto } from "@/lib/media";
import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { Icon } from "@/components/Icon";
import { Cta, PageHero, PostCard, SectionHead } from "@/components/ui";
import { getService, groups, posts, services } from "@/lib/content";

export function generateStaticParams() {
  return services.map((s) => ({ slug: s.slug }));
}
export const dynamicParams = false;

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const s = getService((await params).slug);
  if (!s) return {};
  return { title: s.title, description: s.seo, alternates: { canonical: `/services/${s.slug}` } };
}

export default async function ServicePage({ params }: { params: Promise<{ slug: string }> }) {
  const s = getService((await params).slug);
  if (!s) notFound();
  const related = posts.filter((p) => p.services.includes(s.slug)).slice(0, 3);
  const faqLd = {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    mainEntity: s.faqs.map((f) => ({ "@type": "Question", name: f.q, acceptedAnswer: { "@type": "Answer", text: f.a } })),
  };
  return (
    <>
      <PageHero photo={servicePhoto[s.slug]} eyebrow={groups[s.group].label} title={s.title} text={s.promise}>
        <div className="who">
          <p><strong>Who it is for:</strong> {s.who}</p>
        </div>
        <div className="hero__actions" style={{ marginTop: 28, marginBottom: 0 }}>
          <Link href="/request-a-proposal" className="btn btn--gold">Request a proposal <Icon name="arrow-right" size={18} /></Link>
          <Link href="/contact" className="btn btn--glass">Ask a question</Link>
        </div>
      </PageHero>

      <section className="section">
        <div className="container">
          <SectionHead eyebrow="What we do" title="What is included" />
          <div className="svc-grid">
            {s.subs.map((x, i) => (
              <div key={x.title} className="svc-item reveal" style={{ transitionDelay: `${(i % 3) * 60}ms` }}>
                <h3>{x.title}</h3>
                <p>{x.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="section section--ink">
        <div className="container">
          <SectionHead eyebrow="How it works" title="What working with us looks like" light />
          <ol className="timeline">
            {s.steps.map((x) => (
              <li key={x.title} className="reveal"><h3>{x.title}</h3><p>{x.text}</p></li>
            ))}
          </ol>
        </div>
      </section>

      <section className="section section--soft">
        <div className="container">
          <SectionHead eyebrow="Questions" title="Frequently asked" center />
          <div className="faqs">
            {s.faqs.map((f) => (
              <details key={f.q} className="faq reveal">
                <summary>{f.q}</summary>
                <p>{f.a}</p>
              </details>
            ))}
          </div>
        </div>
      </section>

      {related.length > 0 && (
        <section className="section">
          <div className="container">
            <SectionHead eyebrow="Insights" title={`Reading on ${s.title.toLowerCase()}`} link={{ href: "/insights", label: "All insights" }} />
            <div className="posts">{related.map((p) => <PostCard key={p.slug} post={p} />)}</div>
          </div>
        </section>
      )}

      <section className="section section--soft">
        <div className="container">
          <SectionHead eyebrow="Other services" title="Everything else we help with" />
          <div className="related">
            {services.filter((x) => x.slug !== s.slug).map((x) => (
              <Link key={x.slug} href={`/services/${x.slug}`} className="scard reveal" style={{ background: "#fff", borderColor: "var(--line)" }}>
                <Photo photo={servicePhoto[x.slug]} className="scard__img" sizes="96px" />
                <span className="scard__body"><strong>{x.title}</strong><span>{x.promise}</span></span>
                <Icon name="arrow-up-right" size={18} className="scard__go" />
              </Link>
            ))}
          </div>
        </div>
      </section>
      <Cta />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(faqLd) }} />
    </>
  );
}
