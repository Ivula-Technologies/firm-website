import Link from "next/link";
import { Icon } from "@/components/Icon";
import { Cta, PostCard, SectionHead } from "@/components/ui";
import { Deadlines, PayeTool } from "@/components/Tools";
import { HeroDeadline } from "@/components/HeroDeadline";
import { groups, industries, posts, promises, services, type Group } from "@/lib/content";
import { upcomingDeadlines } from "@/lib/deadlines";
import { site } from "@/lib/site";

const STEPS = [
  { icon: "ear", title: "Listen", text: "We start with your business, not our checklist." },
  { icon: "lightbulb", title: "Clarify", text: "We show you where you stand, in plain figures." },
  { icon: "circle-check", title: "Deliver", text: "We do the work, on time and to standard." },
  { icon: "life-buoy", title: "Stay", text: "We remain available after the report is signed." },
];

export default function Home() {
  const deadlines = upcomingDeadlines();
  const next = deadlines[0];
  const featured = posts.filter((p) => p.meta.featured === "1" || p.meta.start_here === "1");
  const latest = [...featured, ...posts.filter((p) => !featured.includes(p))].slice(0, 3);

  return (
    <>
      <section className="hero">
        <div className="hero__aurora" aria-hidden="true"><span /><span /><span /></div>
        <div className="hero__grid" aria-hidden="true" />
        <div className="container hero__inner">
          <div className="hero__copy">
            <p className="eyebrow eyebrow--light reveal">Nairobi, Kenya</p>
            <h1 className="hero__title reveal">
              Count what <span className="hero__em">matters.</span>
            </h1>
            <p className="hero__text reveal">{site.description}</p>
            <div className="hero__actions reveal">
              <Link href="/request-a-proposal" className="btn btn--gold btn--lg">Request a proposal <Icon name="arrow-right" size={18} /></Link>
              <Link href="/services" className="btn btn--glass btn--lg">Our services</Link>
            </div>
            <ul className="hero__pillars reveal" aria-label="What we help with">
              {(Object.keys(groups) as Group[]).map((g) => (
                <li key={g}><Icon name={groups[g].icon} size={16} /> {groups[g].label}</li>
              ))}
            </ul>
          </div>

          <div className="hero__art" aria-hidden="true">
            <div className="gcard gcard--accounts">
              <p className="gcard__k">Monthly accounts</p>
              <p className="gcard__v">On track <em>+18%</em></p>
              <svg viewBox="0 0 220 64" className="spark">
                <defs>
                  <linearGradient id="sp" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stopColor="#7FAEF2" stopOpacity=".45" /><stop offset="1" stopColor="#7FAEF2" stopOpacity="0" /></linearGradient>
                </defs>
                <path d="M0 52 C 25 48, 35 40, 55 42 S 90 30, 110 32 S 150 14, 170 18 S 205 6, 220 4 L220 64 L0 64Z" fill="url(#sp)" />
                <path d="M0 52 C 25 48, 35 40, 55 42 S 90 30, 110 32 S 150 14, 170 18 S 205 6, 220 4" fill="none" stroke="#7FAEF2" strokeWidth="3" strokeLinecap="round" />
              </svg>
            </div>
            {next && <HeroDeadline initial={next} />}
            <div className="gcard gcard--checks">
              <p className="gcard__k">This month</p>
              <ul>
                <li><Icon name="circle-check" size={16} /> VAT return filed</li>
                <li><Icon name="circle-check" size={16} /> PAYE and SHIF paid</li>
                <li className="is-due"><Icon name="clock" size={16} /> Annual return due</li>
              </ul>
            </div>
            <div className="gcard gcard--impact">
              <span className="gcard__icon"><Icon name="leaf" size={18} /></span>
              <div><p className="gcard__k">Impact report</p><p className="gcard__s">Scope 1 to 3 measured</p></div>
            </div>
            <div className="hero__orb"><span /></div>
          </div>
        </div>
        <div className="marquee" aria-hidden="true">
          <div className="marquee__track">
            {[...services, ...services].map((s, i) => (
              <span key={i}>{s.title}<i /></span>
            ))}
          </div>
        </div>
      </section>

      <section className="section">
        <div className="container">
          <SectionHead
            eyebrow="What we do"
            title="Three things every business must account for"
            text="Your money, your obligations and your impact. We help with all three, so you can get on with running the business."
            link={{ href: "/services", label: "All services" }}
          />
          <div className="bento">
            {(Object.keys(groups) as Group[]).map((g, gi) => (
              <div key={g} className={`bento__col bento__col--${g} reveal`} style={{ transitionDelay: `${gi * 80}ms` }}>
                <div className="bento__head">
                  <span className="bento__icon"><Icon name={groups[g].icon} size={22} /></span>
                  <div>
                    <h3>{groups[g].label}</h3>
                    <p>{groups[g].line}</p>
                  </div>
                </div>
                <div className="bento__items">
                  {services.filter((s) => s.group === g).map((s) => (
                    <Link key={s.slug} href={`/services/${s.slug}`} className="scard">
                      <span className="scard__icon"><Icon name={s.icon} size={20} /></span>
                      <span className="scard__body">
                        <strong>{s.title}</strong>
                        <span>{s.promise}</span>
                      </span>
                      <Icon name="arrow-up-right" size={18} className="scard__go" />
                    </Link>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="section section--ink">
        <div className="container">
          <SectionHead eyebrow="Why JENIQA" title="Four promises we keep" text="How we work on every assignment, from a monthly close to a KRA dispute." light />
          <div className="promises">
            {promises.map((p, i) => (
              <div key={p.title} className="promise reveal" style={{ transitionDelay: `${i * 70}ms` }}>
                <span className="promise__n">0{i + 1}</span>
                <span className="promise__icon"><Icon name={p.icon} size={22} /></span>
                <h3>{p.title}</h3>
                <p>{p.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="section section--soft">
        <div className="container">
          <SectionHead
            eyebrow="Free tools"
            title="Work it out in a minute"
            text="Calculators and reminders for Kenyan businesses. Free, and nothing to sign up for."
            link={{ href: "/free-tools", label: "All free tools" }}
          />
          <div className="tools-teaser">
            <div className="panel reveal">
              <div className="panel__head"><Icon name="calculator" size={20} /><h3>PAYE and take-home pay</h3></div>
              <PayeTool />
            </div>
            <div className="panel panel--ink reveal">
              <div className="panel__head"><Icon name="calendar" size={20} /><h3>KRA deadlines</h3></div>
              <Deadlines initial={deadlines} limit={3} compact />
            </div>
          </div>
        </div>
      </section>

      <section className="section">
        <div className="container">
          <SectionHead eyebrow="Industries" title="Built for the way Kenyan organisations work" link={{ href: "/industries", label: "All industries" }} />
          <div className="industries">
            {industries.map((ind, i) => (
              <Link key={ind.title} href={`/industries#${ind.title.toLowerCase().replace(/[^a-z0-9]+/g, "-")}`} className="icard reveal" style={{ transitionDelay: `${(i % 4) * 60}ms` }}>
                <span className="icard__icon"><Icon name={ind.icon} size={22} /></span>
                <strong>{ind.title}</strong>
                <Icon name="arrow-right" size={16} className="icard__go" />
              </Link>
            ))}
          </div>
        </div>
      </section>

      <section className="section section--soft">
        <div className="container">
          <SectionHead eyebrow="How we work" title="Four steps, the same on every assignment" center />
          <ol className="steps">
            {STEPS.map((s, i) => (
              <li key={s.title} className="step reveal" style={{ transitionDelay: `${i * 80}ms` }}>
                <span className="step__icon"><Icon name={s.icon} size={22} /></span>
                <span className="step__n">Step {i + 1}</span>
                <h3>{s.title}</h3>
                <p>{s.text}</p>
              </li>
            ))}
          </ol>
        </div>
      </section>

      <section className="section">
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
