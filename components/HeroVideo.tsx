"use client";

import { useEffect, useRef } from "react";

/**
 * Muted background video for the home hero. It does not play for people who
 * ask for reduced motion or data saving; they see the still navy background.
 */
export function HeroVideo({ src }: { src: string }) {
  const ref = useRef<HTMLVideoElement>(null);
  useEffect(() => {
    const v = ref.current;
    if (!v) return;
    const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const conn = (navigator as Navigator & { connection?: { saveData?: boolean } }).connection;
    if (reduce || conn?.saveData) return;
    v.src = src;
    v.play().catch(() => {});
  }, [src]);
  return <video ref={ref} className="vhero__video" muted loop playsInline preload="none" aria-hidden="true" tabIndex={-1} />;
}
