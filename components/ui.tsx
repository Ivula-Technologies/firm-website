import { Photo } from "@/components/Photo";
import type { Photo as PhotoT } from "@/lib/media";
import Link from "next/link";
import { Icon } from "./Icon";
import { categories, formatDate, primaryCategory, readingTime, type Post } from "@/lib/content";

export function SectionHead({ eyebrow, title, text, link, center = false, light = false }: {
  eyebrow?: string; title: string; text?: string; link?: { href: string; label: string }; center?: boolean; light?: boolean;
}) {
  return (
    <div className={`shead${center ? " shead--center" : ""}${light ? " shead--light" : ""} reveal`}>
      <div>
        {eyebrow && <p className="eyebrow">{eyebrow}</p>}
        <h2 className="shead__title">{title}</h2>
        {text && <p className="shead__text">{text}</p>}
      </div>
      {link && (
        <Link href={link.href} className="link-arrow">
          {link.label} <Icon name="arrow-right" size={18} />
        </Link>
      )}
    </div>
  );
}

export function PageHero({ eyebrow, title, text, children, photo }: { eyebrow?: string; title: string; text?: string; children?: React.ReactNode; photo?: PhotoT }) {
  return (
    <section className={`phero${photo ? " phero--photo" : ""}`}>
      {photo && <Photo photo={photo} className="phero__img" sizes="100vw" eager />}
      <div className="phero__bg" aria-hidden="true" />
      <div className="container phero__inner">
        {eyebrow && <p className="eyebrow eyebrow--light">{eyebrow}</p>}
        <h1 className="phero__title">{title}</h1>
        {text && <p className="phero__text">{text}</p>}
        {children}
      </div>
    </section>
  );
}

export function PostCard({ post, feature = false }: { post: Post; feature?: boolean }) {
  const cat = primaryCategory(post);
  return (
    <article className={`pcard reveal${feature ? " pcard--feature" : ""}`}>
      <div className="pcard__meta">
        <span className={`tag tag--${cat}`}>{categories[cat]}</span>
        <span>{formatDate(post.date)}</span>
      </div>
      <h3 className="pcard__title">
        <Link href={`/insights/${post.slug}`} className="stretched">{post.title}</Link>
      </h3>
      <p className="pcard__excerpt">{post.excerpt}</p>
      <div className="pcard__foot">
        <span>{post.meta.author_team ? `JENIQA ${post.meta.author_team}` : "JENIQA"}</span>
        <span>{readingTime(post.html)} min read</span>
      </div>
    </article>
  );
}

export function Cta({ title = "Let's look at your numbers together.", text = "Tell us where you are and what is worrying you. We will reply within one working day, in plain language." }: { title?: string; text?: string }) {
  return (
    <section className="cta">
      <div className="container">
        <div className="cta__card reveal">
          <div className="cta__glow" aria-hidden="true" />
          <div className="cta__copy">
            <p className="eyebrow eyebrow--light">Talk to us</p>
            <h2>{title}</h2>
            <p>{text}</p>
          </div>
          <div className="cta__actions">
            <Link href="/request-a-proposal" className="btn btn--gold">Request a proposal <Icon name="arrow-right" size={18} /></Link>
            <Link href="/contact" className="btn btn--glass">Contact us</Link>
          </div>
        </div>
      </div>
    </section>
  );
}
