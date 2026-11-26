import { PageHero } from "./ui";
import { mdToHtml } from "@/lib/content";

export function Legal({ title, md, updated }: { title: string; md: string; updated: string }) {
  return (
    <>
      <PageHero eyebrow="Legal" title={title} />
      <section className="section">
        <div className="container">
          <div className="prose" dangerouslySetInnerHTML={{ __html: mdToHtml(md) }} />
          <p className="notice" style={{ maxWidth: "44rem" }}>Last updated {updated}.</p>
        </div>
      </section>
    </>
  );
}
