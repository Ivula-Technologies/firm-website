import Link from "next/link";
import { Icon } from "@/components/Icon";

export default function NotFound() {
  return (
    <section className="nf">
      <div className="container">
        <p className="nf__code">404</p>
        <h1>We could not find that page</h1>
        <p style={{ color: "var(--muted)", maxWidth: "32em", margin: "0 auto 28px" }}>It may have moved, or the link may be mistyped. These will get you back on track.</p>
        <div className="hero__actions">
          <Link href="/" className="btn btn--primary">Go home <Icon name="arrow-right" size={18} /></Link>
          <Link href="/services" className="btn btn--ghost">Our services</Link>
          <Link href="/contact" className="btn btn--ghost">Contact us</Link>
        </div>
      </div>
    </section>
  );
}
