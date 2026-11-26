import type { Metadata } from "next";
import Link from "next/link";
import { Icon } from "@/components/Icon";
import { Cta, PageHero } from "@/components/ui";
import { groups, services, type Group } from "@/lib/content";
import { Photo } from "@/components/Photo";
import { photos, servicePhoto } from "@/lib/media";

export const metadata: Metadata = {
  title: "Services",
  description: "Accounting, payroll, tax, company secretarial, business advisory and ESG services for Kenyan businesses and institutions.",
  alternates: { canonical: "/services" },
};

export default function ServicesPage() {
  return (
    <>
      <PageHero photo={photos.meetingRoom} eyebrow="Services" title="One firm for your money, your obligations and your impact" text="Six services, one idea: we help you count what matters." />
      {(Object.keys(groups) as Group[]).map((g, gi) => (
        <section key={g} className={`section${gi % 2 ? " section--soft" : ""}`}>
          <div className="container">
            <div className="shead reveal">
              <div>
                <p className="eyebrow">{groups[g].label}</p>
                <h2 className="shead__title">{groups[g].line}</h2>
              </div>
            </div>
            <div className="svc-grid">
              {services.filter((s) => s.group === g).map((s) => (
                <Link key={s.slug} href={`/services/${s.slug}`} className="svc-item svc-item--photo pcard reveal" style={{ textDecoration: "none" }}>
                  <Photo photo={servicePhoto[s.slug]} className="svc-item__img" sizes="(max-width: 600px) 100vw, 33vw" />
                  <h3>{s.title}</h3>
                  <p style={{ fontWeight: 600, color: "var(--navy)", marginBottom: 8 }}>{s.promise}</p>
                  <p>{s.who}</p>
                  <span className="link-arrow" style={{ marginTop: 18 }}>See how we help <Icon name="arrow-right" size={16} /></span>
                </Link>
              ))}
            </div>
          </div>
        </section>
      ))}
      <Cta />
    </>
  );
}
