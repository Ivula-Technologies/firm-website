import Link from "next/link";
import { Logo } from "./Logo";
import { Icon, SocialIcon, WhatsAppIcon } from "./Icon";
import { services } from "@/lib/content";
import { site, telUrl, whatsappUrl } from "@/lib/site";
import { NewsletterForm } from "./Forms";

export function Footer() {
  const socials = (["linkedin", "x", "facebook"] as const).filter((k) => site.social[k]);
  return (
    <footer className="footer">
      <div className="container footer__grid">
        <div className="footer__brand">
          <Link href="/" aria-label={`${site.brand}, home`}><Logo light /></Link>
          <p>{site.description}</p>
          <div className="footer__social">
            {socials.map((k) => (
              <a key={k} href={site.social[k]} target="_blank" rel="noopener" aria-label={`${site.brand} on ${k}`}>
                <SocialIcon kind={k} />
              </a>
            ))}
            <a href={whatsappUrl()} target="_blank" rel="noopener" aria-label={`${site.brand} on WhatsApp`}>
              <WhatsAppIcon size={18} />
            </a>
          </div>
        </div>
        <div>
          <h2 className="footer__h">Services</h2>
          <ul className="footer__list">
            {services.map((s) => (
              <li key={s.slug}><Link href={`/services/${s.slug}`}>{s.title}</Link></li>
            ))}
          </ul>
        </div>
        <div>
          <h2 className="footer__h">Company</h2>
          <ul className="footer__list">
            <li><Link href="/about">About us</Link></li>
            <li><Link href="/industries">Industries</Link></li>
            <li><Link href="/insights">Insights</Link></li>
            <li><Link href="/esg-reporting-hub">ESG reporting hub</Link></li>
            <li><Link href="/free-tools">Free tools</Link></li>
            <li><Link href="/request-a-proposal">Request a proposal</Link></li>
            <li><Link href="/contact">Contact</Link></li>
          </ul>
        </div>
        <div>
          <h2 className="footer__h">Visit or call</h2>
          <ul className="footer__contact">
            <li><Icon name="map-pin" size={18} /><span>{site.address.join(", ")}</span></li>
            <li><Icon name="phone" size={18} /><a href={telUrl()}>{site.phone}</a></li>
            <li><Icon name="mail" size={18} /><a href={`mailto:${site.email}`}>{site.email}</a></li>
            <li><Icon name="clock" size={18} /><span>{site.hours.join(". ")}</span></li>
          </ul>
        </div>
        <div>
          <h2 className="footer__h">Newsletter</h2>
          <p className="footer__note">Tax alerts, ESG updates and practical guides, about twice a month.</p>
          <NewsletterForm />
        </div>
      </div>
      <div className="container footer__bottom">
        <p>&copy; {new Date().getFullYear()} {site.legalName}. All rights reserved.</p>
        <ul>
          <li><Link href="/privacy-policy">Privacy policy</Link></li>
          <li><Link href="/cookie-policy">Cookie policy</Link></li>
          <li><Link href="/terms-of-use">Terms of use and disclaimer</Link></li>
        </ul>
      </div>
      <a className="wa-float" href={whatsappUrl()} target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp">
        <WhatsAppIcon size={26} />
      </a>
    </footer>
  );
}
