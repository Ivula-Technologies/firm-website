"use client";

import { useEffect } from "react";
import { usePathname } from "next/navigation";

/** Fades elements marked .reveal in as they scroll into view. Content stays visible without JavaScript. */
export function RevealObserver() {
  const path = usePathname();
  useEffect(() => {
    const root = document.documentElement;
    root.classList.add("js");
    const els = Array.from(document.querySelectorAll<HTMLElement>(".reveal:not(.is-in)"));
    if (!("IntersectionObserver" in window) || window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      els.forEach((e) => e.classList.add("is-in"));
      return;
    }
    const io = new IntersectionObserver(
      (entries) => entries.forEach((en) => {
        if (en.isIntersecting) {
          en.target.classList.add("is-in");
          io.unobserve(en.target);
        }
      }),
      { rootMargin: "0px 0px -8% 0px", threshold: 0.08 },
    );
    els.forEach((e) => {
      if (e.getBoundingClientRect().top < window.innerHeight) e.classList.add("is-in");
      else io.observe(e);
    });
    return () => io.disconnect();
  }, [path]);
  return null;
}
