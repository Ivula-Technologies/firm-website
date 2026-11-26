"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useState } from "react";
import { Menu, X } from "lucide-react";
import { Logo } from "./Logo";
import { Icon, WhatsAppIcon } from "./Icon";
import { nav, site, telUrl, whatsappUrl } from "@/lib/site";

export function Header() {
  const path = usePathname();
  const [open, setOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const on = () => setScrolled(window.scrollY > 8);
    on();
    window.addEventListener("scroll", on, { passive: true });
    return () => window.removeEventListener("scroll", on);
  }, []);
  useEffect(() => setOpen(false), [path]);
  useEffect(() => {
    document.documentElement.classList.toggle("nav-open", open);
    const esc = (e: KeyboardEvent) => e.key === "Escape" && setOpen(false);
    window.addEventListener("keydown", esc);
    return () => window.removeEventListener("keydown", esc);
  }, [open]);

  const current = (href: string) => path === href || path.startsWith(href + "/");

  return (
    <header className={`header${scrolled ? " is-scrolled" : ""}${open ? " is-open" : ""}`}>
      <div className="topbar">
        <div className="container topbar__inner">
          <span className="topbar__place">Nairobi, Kenya</span>
          <div className="topbar__links">
            <a href={telUrl()}><Icon name="phone" size={14} /> {site.phone}</a>
            <a href={`mailto:${site.email}`}><Icon name="mail" size={14} /> {site.email}</a>
          </div>
        </div>
      </div>
      <div className="container header__inner">
        <Link href="/" className="header__brand" aria-label={`${site.brand}, home`}>
          <Logo />
        </Link>
        <nav className="nav" aria-label="Main">
          <ul>
            {nav.map((n) => (
              <li key={n.href}>
                <Link href={n.href} className={current(n.href) ? "is-current" : undefined} aria-current={current(n.href) ? "page" : undefined}>
                  {n.label}
                </Link>
              </li>
            ))}
          </ul>
          <div className="nav__mobile-extra">
            <a href={telUrl()}><Icon name="phone" size={18} /> {site.phone}</a>
            <a href={`mailto:${site.email}`}><Icon name="mail" size={18} /> {site.email}</a>
            <a href={whatsappUrl()} target="_blank" rel="noopener"><WhatsAppIcon size={18} /> Chat on WhatsApp</a>
          </div>
        </nav>
        <div className="header__actions">
          <Link href="/request-a-proposal" className="btn btn--primary btn--sm">
            <span className="hide-xs">Request a proposal</span>
            <span className="show-xs">Get a proposal</span>
          </Link>
          <button className="nav-toggle" aria-expanded={open} aria-label={open ? "Close menu" : "Open menu"} onClick={() => setOpen(!open)}>
            {open ? <X size={22} /> : <Menu size={22} />}
          </button>
        </div>
      </div>
    </header>
  );
}
