import Link from "next/link";
import { Icon } from "@/components/Icon";
import { Cta, PostCard, SectionHead } from "@/components/ui";
import { Deadlines, PayeTool } from "@/components/Tools";
import { HeroDeadline } from "@/components/HeroDeadline";
import { HeroVideo } from "@/components/HeroVideo";
import { groups, industries, posts, promises, services, type Group } from "@/lib/content";
import { upcomingDeadlines } from "@/lib/deadlines";
import { site } from "@/lib/site";
import { Photo } from "@/components/Photo";
import { industryPhoto, photos } from "@/lib/media";

const STEPS = [
  { title: "Listen", text: "We start with your business, not our checklist." },
  { title: "Clarify", text: "We show you where you stand, in plain figures." },
  { title: "Deliver", text: "We do the work, on time and to standard." },
  { title: "Stay", text: "We remain available after the report is signed." },
];

const slug = (t: string) => t.toLowerCase().replace(/[^a-z0-9]+/g, "-");

export default function Home() {
  const deadlines = upcomingDeadlines();
  const next = deadlines[0];
  const featured = posts.filter((p) => p.meta.featured === "1" || p.meta.start_here === "1");
  const latest = [...featured, ...posts.filter((p) => !featured.includes(p))].slice(0, 3);

  return (
    <>
      <section className="vhero">
        <HeroVideo src={site.heroVideo} />
        <div className="vhero__shade" aria-hidden="true" />
        <div className="container vhero__inner">
          <p className="vhero__place">Accounting, tax, advisory and ESG. Nairobi.</p>
          <h1 className="vhero__title">Count what matters.</h1>
          <p className="vhero__text">{site.description}</p>
          <div className="vhero__actions">
            <Link href="/request-a-proposal" className="btn btn--gold btn--lg">Request a proposal</Link>
            <Link href="/services" className="btn btn--outline-light btn--lg">Our services</Link>
          </div>
        </div>
        <div className="vhero__foot">
          <div className="container vhero__facts">
            <p>{next && <HeroDeadline initial={next} />}</p>
            <p><span className="vhero__k">Response time</span><span className="vhero__v">We reply within one working day</span></p>
            <p><span className="vhero__k">Write to us</span><a className="vhero__v" href={`mailto:${site.email}`}>{site.email}</a></p>
          </div>
        </div>
      </section>

      <section className="section">
        <div className="container split-head">
          <div>
            <h2 className="h-section">Three things every business must account for</h2>
            <p className="lede">Your money, your obligations and your impact. We help with all three, so you can get on with running the business.</p>
            <Link href="/services" className="link-arrow">All services <Icon name="arrow-right" size={16} /></Link>
            <Photo photo={photos.laptopBraids} className="side-photo" sizes="(max-width: 900px) 100vw, 40vw" />
          </div>
          <div className="groups">
            {(Object.keys(groups) as Group[]).map((g) => (
              <div key={g} className="group">
                <h3>{groups[g].label}</h3>
                <p>{groups[g].line}</p>
                <ul>
                  {services.filter((s) => s.group === g).map((s) => (
                    <li key={s.slug}><Link href={`/services/${s.slug}`}>{s.title}</Link></li>
                  ))}
                </ul>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="section section--soft">
        <div className="container">
          <h2 className="h-section">How we work with every client</h2>
          <ol className="numbered">
            {promises.map((p) => (
              <li key={p.title}>
                <h3>{p.title}</h3>
                <p>{p.text}</p>
              </li>
            ))}
          </ol>
        </div>
      </section>

      <section className="section">
        <div className="container">
          <SectionHead
            eyebrow="Free tools"
            title="Work it out in a minute"
            text="Calculators and reminders for Kenyan businesses. Free, and nothing to sign up for."
            link={{ href: "/free-tools", label: "All free tools" }}
          />
          <div className="tools-teaser">
            <div className="panel">
              <div className="panel__head"><h3>PAYE and take-home pay</h3></div>
              <PayeTool />
            </div>
            <div className="panel panel--ink">
              <div className="panel__head"><h3>KRA deadlines</h3></div>
              <Deadlines initial={deadlines} limit={3} compact />
            </div>
          </div>
        </div>
      </section>

      <section className="section section--soft">
        <div className="container">
          <SectionHead title="Who we work with" text="Kenyan organisations of every size, each with its own rules and reporting." link={{ href: "/industries", label: "All industries" }} />
          <ul className="itiles">
            {industries.map((ind) => (
              <li key={ind.title}>
                <Link href={`/industries#${slug(ind.title)}`}>
                  <Photo photo={industryPhoto[ind.title]} className="itiles__img" sizes="(max-width: 560px) 50vw, 25vw" />
                  <span>{ind.title}</span>
                </Link>
              </li>
            ))}
          </ul>
        </div>
      </section>

      <section className="section">
        <div className="container">
          <h2 className="h-section">Four steps, the same on every assignment</h2>
          <ol className="numbered numbered--steps">
            {STEPS.map((s) => (
              <li key={s.title}>
                <h3>{s.title}</h3>
                <p>{s.text}</p>
              </li>
            ))}
          </ol>
        </div>
      </section>

      <section className="section section--soft">
        <div className="container">
          <SectionHead eyebrow="Insights" title="Latest from our tax and ESG teams" link={{ href: "/insights", label: "All insights" }} />
          <div className="posts">
            {latest.map((p, i) => <PostCard key={p.slug} post={p} feature={i === 0} />)}
          </div>
        </div>
      </section>

      <Cta />
    </>
  );
}
