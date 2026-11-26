import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { Icon } from "@/components/Icon";
import { Cta, PostCard, SectionHead } from "@/components/ui";
import { categories, formatDate, getPost, getService, posts, primaryCategory, readingTime } from "@/lib/content";
import { site } from "@/lib/site";

export function generateStaticParams() {
  return posts.map((p) => ({ slug: p.slug }));
}
export const dynamicParams = false;

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const p = getPost((await params).slug);
  if (!p) return {};
  return { title: p.title, description: p.excerpt, alternates: { canonical: `/insights/${p.slug}` }, openGraph: { type: "article", publishedTime: p.date } };
}

const COURTS: Record<string, string> = { tat: "Tax Appeals Tribunal", hc: "High Court", coa: "Court of Appeal", sc: "Supreme Court" };
const OUTCOMES: Record<string, string> = { taxpayer: "In favour of the taxpayer", kra: "In favour of KRA", partly: "Partly allowed" };

export default async function PostPage({ params }: { params: Promise<{ slug: string }> }) {
  const p = getPost((await params).slug);
  if (!p) notFound();
  const cat = primaryCategory(p);
  const m = p.meta;
  const isCase = p.cats.includes("case-analysis");
  const series = posts.filter((x) => x.meta.series_part).sort((a, b) => +a.meta.series_part - +b.meta.series_part);
  const related = posts.filter((x) => x.slug !== p.slug && x.cats.some((c) => p.cats.includes(c))).slice(0, 3);
  const ld = {
    "@context": "https://schema.org",
    "@type": "Article",
    headline: p.title,
    description: p.excerpt,
    datePublished: p.date,
    author: { "@type": "Organization", name: `${site.brand} ${m.author_team || ""}`.trim() },
    publisher: { "@id": `${site.url}/#organization` },
    mainEntityOfPage: `${site.url}/insights/${p.slug}`,
  };

  return (
    <>
      <section className="phero">
        <div className="phero__bg" aria-hidden="true" />
        <div className="container phero__inner">
          <ol className="crumbs">
            <li><Link href="/">Home</Link></li>
            <li><Link href="/insights">Insights</Link></li>
            <li>{categories[cat]}</li>
          </ol>
          <span className={`tag tag--${cat}`}>{categories[cat]}</span>
          <h1 className="phero__title" style={{ maxWidth: "24ch", marginTop: 18 }}>{p.title}</h1>
          <p className="phero__text">{p.excerpt}</p>
          <p className="phero__text" style={{ marginTop: 18, fontSize: ".95rem", color: "#8EA3C2" }}>
            {site.brand} {m.author_team} · {formatDate(p.date)} · {readingTime(p.html)} min read
          </p>
        </div>
      </section>

      <section className="section">
        <div className="container article">
          <article>
            {isCase && (
              <>
                <div className="case reveal">
                  <dl>
                    {m.case_name && <><dt>Case</dt><dd>{m.case_name}</dd></>}
                    {m.case_citation && <><dt>Citation</dt><dd>{m.case_citation}</dd></>}
                    {m.case_court && <><dt>Court</dt><dd>{COURTS[m.case_court] || m.case_court}</dd></>}
                    {m.case_date && <><dt>Decided</dt><dd>{formatDate(m.case_date)}</dd></>}
                    {m.case_outcome && <><dt>Outcome</dt><dd>{OUTCOMES[m.case_outcome] || m.case_outcome}</dd></>}
                    {m.case_link && <><dt>Judgment</dt><dd><a href={m.case_link} target="_blank" rel="noopener">Read on Kenya Law</a></dd></>}
                  </dl>
                </div>
                {m.case_means && (
                  <div className="means reveal">
                    <h2>What it means for you</h2>
                    <ul>{m.case_means.split("\n").filter(Boolean).map((l) => <li key={l}><Icon name="circle-check" size={18} /> {l}</li>)}</ul>
                  </div>
                )}
              </>
            )}
            <div className="prose" dangerouslySetInnerHTML={{ __html: p.html }} />
            {isCase && (
              <div className="prose">
                {m.case_issues && <><h2>The issues</h2><ul>{m.case_issues.split("\n").filter(Boolean).map((l) => <li key={l}>{l.replace(/^- /, "")}</li>)}</ul></>}
                {m.case_facts && <><h2>The facts</h2>{m.case_facts.split(/\n+/).map((l) => <p key={l}>{l}</p>)}</>}
                {m.case_decision && <><h2>The decision</h2><p>{m.case_decision}</p></>}
              </div>
            )}
            <p className="notice">This article is general information only and is not professional advice. Tax and reporting rules change and every situation is different, so please talk to us before you act on it.</p>
          </article>
          <aside className="aside">
            {m.series_part && (
              <div className="aside__card">
                <h2>ESG reporting for Kenyan businesses</h2>
                <ol style={{ margin: 0, paddingLeft: "1.2em", display: "grid", gap: 8, fontSize: ".94rem" }}>
                  {series.map((x) => (
                    <li key={x.slug}>{x.slug === p.slug ? <strong>{x.title}</strong> : <Link href={`/insights/${x.slug}`}>{x.title}</Link>}</li>
                  ))}
                </ol>
              </div>
            )}
            {p.services.length > 0 && (
              <div className="aside__card">
                <h2>Related services</h2>
                <ul className="pills">
                  {p.services.map((slug) => {
                    const s = getService(slug);
                    return s ? <li key={slug}><Link href={`/services/${slug}`}>{s.title}</Link></li> : null;
                  })}
                </ul>
              </div>
            )}
            <div className="aside__card aside__card--ink">
              <h2>Does this affect you?</h2>
              <p>Tell us your situation and we will reply within one working day.</p>
              <Link href="/contact" className="btn btn--gold btn--block">Ask our team</Link>
            </div>
          </aside>
        </div>
      </section>

      {related.length > 0 && (
        <section className="section section--soft">
          <div className="container">
            <SectionHead eyebrow="Keep reading" title="Related insights" link={{ href: "/insights", label: "All insights" }} />
            <div className="posts">{related.map((x) => <PostCard key={x.slug} post={x} />)}</div>
          </div>
        </section>
      )}
      <Cta />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(ld) }} />
    </>
  );
}
